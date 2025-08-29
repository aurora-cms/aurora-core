<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Infrastructure\ContentRepository\NodeTypes;

use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\Exception\InvalidNodeTypeDefinition;
use PHPUnit\Framework\TestCase;

final class DefinitionLoaderTest extends TestCase
{
    public function testLoadsYamlAndJsonAndNormalizesStructure(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_node_types_'.bin2hex(random_bytes(4));
        @mkdir($dir);

        // base yaml
        $yaml = <<<YAML
base:
  properties:
    title: { type: string }
YAML;
        file_put_contents($dir.DIRECTORY_SEPARATOR.'base.yaml', $yaml);

        // child json
        $json = json_encode([
            'child' => [
                'extends' => 'base',
                'properties' => [
                    'views' => ['type' => 'int'],
                ],
            ],
        ], JSON_PRETTY_PRINT);
        file_put_contents($dir.DIRECTORY_SEPARATOR.'child.json', (string) $json);

        $loader = new DefinitionLoader([$dir]);
        $defs = $loader->load();

        self::assertArrayHasKey('base', $defs);
        self::assertArrayHasKey('child', $defs);
        self::assertSame(null, $defs['base']['extends'] ?? null);
        self::assertSame('base', $defs['child']['extends']);
        if (!isset($defs['base']['properties'])) {
            self::fail('base properties not set or not an array');
        }
        self::assertArrayHasKey('title', $defs['base']['properties']);
        if (!isset($defs['child']['properties'])) {
            self::fail('child properties not set or not an array');
        }
        self::assertArrayHasKey('views', $defs['child']['properties']);
    }

    public function testRejectsInvalidPropertyTypeFormat(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_node_types_'.bin2hex(random_bytes(4));
        @mkdir($dir);
        $yaml = <<<YAML
oops:
  properties:
    bad: { type: [ 1, 2, 3 ] }
YAML;
        file_put_contents($dir.DIRECTORY_SEPARATOR.'oops.yaml', $yaml);

        $loader = new DefinitionLoader([$dir]);
        $this->expectException(InvalidNodeTypeDefinition::class);
        $this->expectExceptionMessage('type must be a string');
        $loader->load();
    }
}

