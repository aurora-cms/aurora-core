<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Infrastructure\Persistence\ContentRepository;

use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use Aurora\Infrastructure\Persistence\ContentRepository\ConfigNodeTypeRepository;
use PHPUnit\Framework\TestCase;

final class ConfigNodeTypeRepositoryTest extends TestCase
{
    public function testReadsFromWarmCache(): void
    {
        $cacheFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_node_types_cache_'.bin2hex(random_bytes(4)).'.json';

        $payload = [
            [
                'name' => 'base',
                'properties' => [
                    ['name' => 'title', 'type' => 'string', 'nullable' => false, 'multiple' => false],
                ],
            ],
            [
                'name' => 'article',
                'properties' => [
                    ['name' => 'views', 'type' => 'int', 'nullable' => false, 'multiple' => false],
                ],
            ],
        ];
        file_put_contents($cacheFile, json_encode($payload, JSON_PRETTY_PRINT));

        // Use real instances; repo should prefer cache and not require them.
        $loader = new DefinitionLoader([sys_get_temp_dir().DIRECTORY_SEPARATOR.'nonexistent_'.bin2hex(random_bytes(3))]);
        $resolver = new NodeTypeResolver();
        $repo = new ConfigNodeTypeRepository($loader, $resolver, $cacheFile);
        self::assertInstanceOf(NodeTypeRepository::class, $repo);

        $all = $repo->all();
        self::assertCount(2, $all);
        $article = $repo->get('article');
        self::assertInstanceOf(NodeType::class, $article);
        self::assertTrue($article->has('views'));

        // cleanup
        @unlink($cacheFile);
    }
}
