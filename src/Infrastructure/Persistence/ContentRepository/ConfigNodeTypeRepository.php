<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Persistence\ContentRepository;

use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Exception\NodeTypeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;

/**
 * NodeType repository backed by config files with optional cache file.
 */
final class ConfigNodeTypeRepository implements NodeTypeRepository
{
    /** @var array<string, NodeType>|null */
    private ?array $types = null;

    public function __construct(
        private readonly DefinitionLoader $loader,
        private readonly NodeTypeResolver $resolver,
        private readonly ?string $cacheFile = null,
    ) {
    }

    public function register(NodeType $type): void
    {
        $this->ensureLoaded();
        $this->types[$type->name] = $type;
    }

    public function get(string $name): NodeType
    {
        $this->ensureLoaded();
        if (!isset($this->types[$name])) {
            throw new NodeTypeNotFound(\sprintf('Node type "%s" is not registered.', $name));
        }

        return $this->types[$name];
    }

    /**
     * @return NodeType[]
     */
    public function all(): array
    {
        $this->ensureLoaded();

        return array_values($this->types ?? []);
    }

    public function has(string $name): bool
    {
        $this->ensureLoaded();

        return isset($this->types[$name]);
    }

    private function ensureLoaded(): void
    {
        if (null !== $this->types) {
            return;
        }

        // Prefer warm cache if present
        $cacheFile = $this->cacheFile ?? $this->defaultCacheFilePath();
        if ($cacheFile && is_file($cacheFile)) {
            $json = file_get_contents($cacheFile) ?: '';
            $raw = json_decode($json, true);
            if (\is_array($raw)) {
                $typed = [];
                foreach ($raw as $item) {
                    if (!\is_array($item)) {
                        continue;
                    }
                    $name = $item['name'] ?? null;
                    $properties = $item['properties'] ?? null;
                    if (!\is_string($name) || !\is_array($properties)) {
                        continue;
                    }
                    $propList = [];
                    foreach ($properties as $p) {
                        if (!\is_array($p)) {
                            continue;
                        }
                        $pn = $p['name'] ?? null;
                        $pt = $p['type'] ?? null;
                        $pnul = $p['nullable'] ?? null;
                        $pmul = $p['multiple'] ?? null;
                        if (!\is_string($pn) || !\is_string($pt) || !\is_bool($pnul) || !\is_bool($pmul)) {
                            continue;
                        }
                        $propList[] = [
                            'name' => $pn,
                            'type' => $pt,
                            'nullable' => $pnul,
                            'multiple' => $pmul,
                        ];
                    }
                    $typed[] = [
                        'name' => $name,
                        'properties' => $propList,
                    ];
                }
                /** @var list<array{name: string, properties: list<array{name: string, type: string, nullable: bool, multiple: bool}>}> $typed */
                $this->types = $this->hydrateFromResolved($typed);

                return;
            }
        }

        // Resolve from source definitions
        $resolved = $this->resolver->resolve($this->loader->load());
        $this->types = $resolved;
    }

    private function defaultCacheFilePath(): string
    {
        $projectDir = \dirname(__DIR__, 3); // src/Infrastructure/Persistence -> up 3 = project
        $env = getenv('APP_ENV') ?: 'dev';
        $candidate = $projectDir.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$env.DIRECTORY_SEPARATOR.'aurora_node_types.json';
        if (is_file($candidate)) {
            return $candidate;
        }

        $fallback = $projectDir.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'aurora_node_types.json';

        return $fallback;
    }

    /**
     * @param list<array{name: string, properties: list<array{name: string, type: string, nullable: bool, multiple: bool}>}> $raw
     *
     * @return array<string, NodeType>
     */
    private function hydrateFromResolved(array $raw): array
    {
        $map = [];
        /** @var array{name: string, properties: list<array{name: string, type: string, nullable: bool, multiple: bool}>} $t */
        foreach ($raw as $t) {
            $props = [];
            /** @var array{name: string, type: string, nullable: bool, multiple: bool} $p */
            foreach ($t['properties'] as $p) {
                $type = PropertyType::from($p['type']);
                $props[] = new PropertyDefinition($p['name'], $type, (bool) $p['nullable'], (bool) $p['multiple']);
            }
            $nt = new NodeType($t['name'], $props);
            $map[$nt->name] = $nt;
        }

        return $map;
    }
}
