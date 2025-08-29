<?php

namespace Aurora\Tests\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\ContentRepository\UseCase\GetNodeByPathHandler;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryWorkspaceRepository;
use Exception;
use PHPUnit\Framework\TestCase;

class GetNodeByPathHandlerTest extends TestCase
{
    private Workspace $ws;
    private WorkspaceRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new InMemoryWorkspaceRepository();
        $this->ws = Workspace::initialize(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('rootnode-1'),
            new NodeType('root')
        );
        $this->repo->save($this->ws);
    }

    public function testGetRootNodeByPath(): void
    {
        $handler = new GetNodeByPathHandler($this->repo);
        $response = $handler('draft', [], '/');
        $this->assertSame('rootnode-1', $response->nodeId);
        $this->assertSame('/', $response->path);
        $this->assertSame('root', $response->nodeType);
        $this->assertSame([], $response->properties);
    }

    public function testGetChildNodeByPath(): void
    {
        try {
            $this->ws->createNode(
                NodeId::fromString('childnode-1'),
                new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING, false)]),
                ['title' => 'Child Node'],
                NodeId::fromString('rootnode-1'),
                'child'
            );
        } catch (Exception $e) {
            $this->fail('Exception should not be thrown: ' . $e->getMessage());
        }

        $handler = new GetNodeByPathHandler($this->repo);
        $response = $handler('draft', [], '/child');
        $this->assertSame('childnode-1', $response->nodeId);
        $this->assertSame('/child', $response->path);
        $this->assertSame('document', $response->nodeType);
        $this->assertSame(['title' => 'Child Node'], $response->properties);
    }

    public function testGetNonExistentNodeByPath(): void
    {
        $handler = new GetNodeByPathHandler($this->repo);
        $this->expectException(NodeNotFound::class);
        $handler('draft', [], '/nonexistent');
    }
}
