<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Application\ContentRepository\Port;

use Aurora\Application\ContentRepository\Port\NodeRepository;
use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Node;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use PHPUnit\Framework\TestCase;

abstract class NodeRepositoryContractTest extends TestCase
{
    abstract protected function createNodeRepository(NodeTypeRepository $types): NodeRepository;

    abstract protected function createNodeTypeRepository(): NodeTypeRepository;

    public function testPersistFetchChildrenAndRemove(): void
    {
        $types = $this->createNodeTypeRepository();
        // seed types
        $types->register(new NodeType('root', []));
        $types->register(new NodeType('article', [
            new PropertyDefinition('title', PropertyType::STRING, false, false),
        ]));

        $repo = $this->createNodeRepository($types);

        $ws = WorkspaceId::fromString('draft');
        $dims = new DimensionSet([]);

        // root node
        $root = new Node(NodeId::fromString('rootnode-1'), $ws, $dims, $types->get('root'), NodePath::root(), []);
        $repo->save($root, null, null);

        // child
        $n1 = new Node(NodeId::fromString('childnode-n1'), $ws, $dims, $types->get('article'), NodePath::fromString('/news'), ['title' => 'Hello']);
        $repo->save($n1, $root->id, 'news');

        // Assertions
        $loaded = $repo->get($n1->id);
        self::assertSame('childnode-n1', (string) $loaded->id);
        self::assertSame('/news', (string) $loaded->path);
        self::assertSame('Hello', $loaded->properties['title'] ?? null);

        $byPath = $repo->getByPath($ws, $dims, NodePath::fromString('/news'));
        self::assertSame('childnode-n1', (string) $byPath->id);

        $children = $repo->childrenOf($root->id);
        self::assertCount(1, $children);
        self::assertSame('childnode-n1', (string) $children[0]->id);

        // update and save
        $updated = $loaded->withProperty('title', 'World');
        $repo->save($updated);
        self::assertSame('World', $repo->get($updated->id)->properties['title'] ?? null);

        // remove
        $repo->remove($root->id, true);
        $this->expectException(\Aurora\Domain\ContentRepository\Exception\NodeNotFound::class);
        $repo->get($root->id);
    }
}

