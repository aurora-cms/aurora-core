<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Interface\Cli\Command;

use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'node-types:list', description: 'List resolved NodeTypes with properties')]
final class NodeTypesListCommand extends Command
{
    public function __construct(
        private readonly DefinitionLoader $loader,
        private readonly NodeTypeResolver $resolver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('details', null, InputOption::VALUE_NONE, 'Show properties and inheritance details');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Loading node types...</info>');
        try {
            $defs = $this->loader->load();
            $resolved = $this->resolver->resolve($defs);
        } catch (\Throwable $e) {
            $output->writeln('<error>Failed to load node types:</error> '.$e->getMessage());

            return Command::FAILURE;
        }

        $showDetails = (bool) $input->getOption('details');

        // Build quick lookup for parent chain
        $extends = [];
        foreach ($defs as $name => $def) {
            $extends[$name] = isset($def['extends']) ? (string) $def['extends'] : null;
        }

        foreach ($resolved as $name => $type) {
            if (!$showDetails) {
                $output->writeln(\sprintf('<info>%s</info>', $type->name));
                continue;
            }

            $chain = $this->buildInheritanceChain($name, $extends);
            $label = $type->name;
            if ($chain) {
                $label .= ' (extends: '.implode(' -> ', $chain).')';
            }
            $output->writeln(\sprintf('<info>%s</info>', $label));

            // reflect property definitions
            [$props, $ownSet, $parentSet] = $this->introspectProperties($name, $type, $resolved, $defs);

            foreach ($props as $p) {
                $tags = [];
                $isOwn = isset($ownSet[$p['name']]);
                $inParent = isset($parentSet[$p['name']]);
                $isOverride = $isOwn && $inParent;
                if ($isOverride) {
                    $tags[] = 'override';
                } elseif ($isOwn) {
                    $tags[] = 'own';
                } elseif ($inParent) {
                    $tags[] = 'inherited';
                }

                $flags = [];
                if ($p['nullable']) {
                    $flags[] = 'nullable';
                }
                if ($p['multiple']) {
                    $flags[] = 'multiple';
                }

                $meta = [];
                if ($tags) {
                    $meta[] = implode(',', $tags);
                }
                if ($flags) {
                    $meta[] = implode(',', $flags);
                }

                $suffix = $meta ? ' ['.implode(' | ', $meta).']' : '';

                $output->writeln(\sprintf('  - %s: %s%s', $p['name'], $p['type'], $suffix));
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Build inheritance chain from closest parent up to root parent.
     *
     * @param array<string, string|null> $extends
     *
     * @return list<string>
     */
    private function buildInheritanceChain(string $name, array $extends): array
    {
        $chain = [];
        $cur = $extends[$name] ?? null;
        while ($cur) {
            $chain[] = $cur;
            $cur = $extends[$cur] ?? null;
        }

        return $chain;
    }

    /**
     * Introspects a resolved NodeType, returning flattened property data plus own/parent sets.
     *
     * @param array<string, NodeType>                                                                                                        $resolved
     * @param array<string, array{extends?: string|null, properties?: array<string, array{type: string, nullable?: bool, multiple?: bool}>}> $defs
     *
     * @return array{0: list<array{name: string, type: string, nullable: bool, multiple: bool}>, 1: array<string, true>, 2: array<string, true>}
     */
    private function introspectProperties(string $name, NodeType $type, array $resolved, array $defs): array
    {
        // Reflect property definitions from NodeType
        $rp = new \ReflectionProperty(NodeType::class, 'definitions');
        $rp->setAccessible(true);
        /** @var array<string, PropertyDefinition> $defsMap */
        $defsMap = $rp->getValue($type);
        $props = [];
        foreach ($defsMap as $pd) {
            $props[] = [
                'name' => $pd->name,
                'type' => $pd->type->value,
                'nullable' => $pd->nullable,
                'multiple' => $pd->multiple,
            ];
        }
        // Sort for stable output
        usort($props, static fn ($a, $b) => $a['name'] <=> $b['name']);

        // Identify own properties as declared in raw definitions for this type
        $ownSet = [];
        $rawProps = $defs[$name]['properties'] ?? [];
        foreach ($rawProps as $pName => $_) {
            $ownSet[(string) $pName] = true;
        }

        // Build merged parent property set (chain to root)
        $parentSet = [];
        $parent = $defs[$name]['extends'] ?? null;
        while ($parent) {
            if (!isset($resolved[$parent])) {
                break;
            }
            $pType = $resolved[$parent];
            $pMap = $rp->getValue($pType);
            /** @var array<string, PropertyDefinition> $pMap */
            foreach ($pMap as $propName => $pd) {
                $parentSet[$propName] = true;
            }
            $parent = $defs[$parent]['extends'] ?? null;
        }

        return [$props, $ownSet, $parentSet];
    }
}
