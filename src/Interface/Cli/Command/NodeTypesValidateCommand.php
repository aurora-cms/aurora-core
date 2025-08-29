<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Interface\Cli\Command;

use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'node-types:validate', description: 'Validate NodeType definitions and inheritance')]
final class NodeTypesValidateCommand extends Command
{
    public function __construct(
        private readonly DefinitionLoader $loader,
        private readonly NodeTypeResolver $resolver,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $defs = $this->loader->load();
            $this->resolver->resolve($defs);
        } catch (\Throwable $e) {
            $output->writeln('<error>Validation failed:</error> '.$e->getMessage());

            return Command::FAILURE;
        }

        $output->writeln('<info>All node type definitions are valid.</info>');

        return Command::SUCCESS;
    }
}
