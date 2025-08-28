<?php

namespace Aurora\Tests\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\DeleteNodeRequest;
use Aurora\Application\ContentRepository\UseCase\DeleteNodeHandler;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\NoopTransactionBoundary;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryWorkspaceRepository;
use PHPUnit\Framework\TestCase;

final class DeleteNodeHandlerTest extends TestCase
{
    private InMemoryWorkspaceRepository $repo;
    private NoopTransactionBoundary $tx;
    private DeleteNodeHandler $handler;
    private Workspace $ws;

    protected function setUp(): void
    {
        $this->repo = new InMemoryWorkspaceRepository();
        $this->tx = new NoopTransactionBoundary();
        $this->ws = Workspace::initialize(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('rootnode-1'),
            new NodeType('document')
        );
        $this->repo->save($this->ws);
    }

    public function testDeleteNode(): void
    {
        $this->ws->createNode(
            NodeId::fromString('childnode-1'),
            new NodeType('document'),
            [],
            NodeId::fromString('rootnode-1'),
            'a'
        );
        $h = new DeleteNodeHandler($this->repo, $this->tx);
        $r = $h(new DeleteNodeRequest('draft', [], NodeId::fromString('childnode-1')));
        $this->assertSame('childnode-1', $r->nodeId);
        $this->assertFalse($r->cascade);
        $this->assertSame([], $r->deletedNodeIds);
    }

    public function testDeleteNodeCascade(): void
    {
        $this->ws->createNode(
            NodeId::fromString('childnode-1'),
            new NodeType('document'),
            [],
            NodeId::fromString('rootnode-1'),
            'a'
        );
        $this->ws->createNode(
            NodeId::fromString('grandchildnode-1'),
            new NodeType('document'),
            [],
            NodeId::fromString('childnode-1'),
            'b'
        );
        $h = new DeleteNodeHandler($this->repo, $this->tx);
        $r = $h(new DeleteNodeRequest('draft', [], NodeId::fromString('childnode-1'), true));
        $this->assertSame('childnode-1', $r->nodeId);
        $this->assertTrue($r->cascade);
        $this->assertCount(2, $r->deletedNodeIds);
        $this->assertContains('childnode-1', $r->deletedNodeIds);
        $this->assertContains('grandchildnode-1', $r->deletedNodeIds);
    }

    public function testDeleteNodeNotFound(): void
    {
        $h = new DeleteNodeHandler($this->repo, $this->tx);
        $this->expectException(NodeNotFound::class);
        $h(new DeleteNodeRequest('draft', [], NodeId::fromString('nonexisting-node')));
    }
}
