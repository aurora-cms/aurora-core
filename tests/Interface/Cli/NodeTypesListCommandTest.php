<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Interface\Cli;

use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use Aurora\Interface\Cli\Command\NodeTypesListCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class NodeTypesListCommandTest extends TestCase
{
    public function testDetailsOutputShowsInheritanceAndOverrides(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_cli_defs_'.bin2hex(random_bytes(4));
        @mkdir($dir);
        $yaml = <<<YAML
base:
  properties:
    title: { type: string }
    views: { type: int }
article:
  extends: base
  properties:
    views: { type: int, nullable: true }
    tags: { type: string, multiple: true }
YAML;
        file_put_contents($dir.DIRECTORY_SEPARATOR.'defs.yaml', $yaml);

        $loader = new DefinitionLoader([$dir]);
        $resolver = new NodeTypeResolver();

        $command = new NodeTypesListCommand($loader, $resolver);
        $tester = new CommandTester($command);
        $exit = $tester->execute(['--details' => true]);

        self::assertSame(0, $exit);
        $display = $tester->getDisplay();

        self::assertStringContainsString('article (extends: base)', $display);
        self::assertStringContainsString('views: int [override', $display);
        self::assertStringContainsString('tags: string [own', $display);
        self::assertStringContainsString('title: string [inherited', $display);
        // cleanup temp
        @unlink($dir.DIRECTORY_SEPARATOR.'defs.yaml');
        @rmdir($dir);
    }
}
