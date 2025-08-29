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
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypesCacheWarmer;
use PHPUnit\Framework\TestCase;

final class NodeTypesCacheWarmerTest extends TestCase
{
    public function testWarmUpWritesResolvedCache(): void
    {
        $cacheFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_node_types_cachewarmer_'.bin2hex(random_bytes(4)).'.json';

        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_cachewarmer_defs_'.bin2hex(random_bytes(4));
        @mkdir($dir);
        $yaml = <<<YAML
base:
  properties:
    title: { type: string }
article:
  extends: base
  properties:
    views: { type: int }
YAML;
        file_put_contents($dir.DIRECTORY_SEPARATOR.'defs.yaml', $yaml);

        $loader = new DefinitionLoader([$dir]);

        $resolver = new NodeTypeResolver();
        $warmer = new NodeTypesCacheWarmer($loader, $resolver, $cacheFile);
        $warmer->warmUp(sys_get_temp_dir());

        self::assertFileExists($cacheFile);
        $json = file_get_contents($cacheFile);
        self::assertIsString($json);
        $data = json_decode((string) $json, true);
        self::assertIsArray($data);
        self::assertGreaterThanOrEqual(2, count($data));
        if (!isset($data[1]) || !is_array($data[1])) {
            self::fail('Second node type not set');
        }
        if (!isset($data[1]['name'])) {
            self::fail('Second node type name not set');
        }
        self::assertSame('article', $data[1]['name']);

        @unlink($cacheFile);
        @unlink($dir.DIRECTORY_SEPARATOR.'defs.yaml');
        @rmdir($dir);
    }
}
