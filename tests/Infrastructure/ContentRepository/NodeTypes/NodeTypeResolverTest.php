<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Infrastructure\ContentRepository\NodeTypes;

use Aurora\Infrastructure\ContentRepository\NodeTypes\Exception\InvalidNodeTypeDefinition;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use PHPUnit\Framework\TestCase;

final class NodeTypeResolverTest extends TestCase
{
    public function testInheritanceMergesAndOverridesProperties(): void
    {
        $defs = [
            'base' => [
                'properties' => [
                    'title' => ['type' => 'string'],
                    'views' => ['type' => 'int'],
                ],
            ],
            'article' => [
                'extends' => 'base',
                'properties' => [
                    // override type and flags
                    'views' => ['type' => 'int', 'nullable' => true],
                    'tags' => ['type' => 'string', 'multiple' => true],
                ],
            ],
        ];

        $resolver = new NodeTypeResolver();
        $resolved = $resolver->resolve($defs);

        self::assertArrayHasKey('article', $resolved);
        $article = $resolved['article'];

        // The inherited property
        self::assertTrue($article->has('title'));
        // The overridden property exists
        self::assertTrue($article->has('views'));
        // The new property exists
        self::assertTrue($article->has('tags'));
    }

    public function testUnknownParentThrows(): void
    {
        $defs = [
            'child' => [
                'extends' => 'missing',
                'properties' => [
                    'foo' => ['type' => 'string'],
                ],
            ],
        ];

        $resolver = new NodeTypeResolver();

        $this->expectException(InvalidNodeTypeDefinition::class);
        $this->expectExceptionMessage('Unknown parent node type');
        $resolver->resolve($defs);
    }

    public function testCircularInheritanceThrows(): void
    {
        $defs = [
            'a' => [
                'extends' => 'b',
                'properties' => [],
            ],
            'b' => [
                'extends' => 'a',
                'properties' => [],
            ],
        ];

        $resolver = new NodeTypeResolver();

        $this->expectException(InvalidNodeTypeDefinition::class);
        $this->expectExceptionMessage('Circular inheritance');
        $resolver->resolve($defs);
    }

    public function testUnknownPropertyTypeThrows(): void
    {
        $defs = [
            't' => [
                'properties' => [
                    'foo' => ['type' => 'unknown-type'],
                ],
            ],
        ];

        $resolver = new NodeTypeResolver();

        $this->expectException(InvalidNodeTypeDefinition::class);
        $this->expectExceptionMessage('Unknown property type');
        $resolver->resolve($defs);
    }
}
