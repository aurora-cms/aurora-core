<?php

namespace Aurora\Tests\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\MoveNodeRequest;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\ContentRepository\UseCase\MoveNodeHandler;
use Aurora\Application\Contract\TransactionBoundary;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\NoopTransactionBoundary;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryWorkspaceRepository;
use Exception;
use PHPUnit\Framework\TestCase;

const DOCUMENT_TYPE = new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING, false)]);
class MoveNodeHandlerTest extends TestCase
{
    private WorkspaceRepository $repo;
    private Workspace $ws;
    private TransactionBoundary $tx;

    protected function setUp(): void
    {
        $this->repo = new InMemoryWorkspaceRepository();
        $this->ws = Workspace::initialize(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('rootnode-1'),
            new NodeType('root'));
        $this->tx = new NoopTransactionBoundary();
        $this->repo->save($this->ws);
    }

    public function testMoveNode(): void
    {
        try {
            $this->ws->createNode(
                NodeId::fromString('parentnode-1'),
                DOCUMENT_TYPE,
                ['title' => 'Parent 1'],
                NodeId::fromString('rootnode-1'),
                'a'
            );
            $this->ws->createNode(
                NodeId::fromString('parentnode-2'),
                DOCUMENT_TYPE,
                ['title' => 'Child 1'],
                NodeId::fromString('rootnode-1'),
                'b'
            );
        } catch (Exception $e) {
            $this->fail('Exception should not be thrown: ' . $e->getMessage());
        }
        $this->repo->save($this->ws);

        $h = new MoveNodeHandler($this->repo, $this->tx);
        $r = $h(new MoveNodeRequest($this->ws->id, [], 'parentnode-2', 'parentnode-1'));
        $this->assertEquals('parentnode-2', $r->nodeId);
        $this->assertEquals('/a/b', $r->newPath);
    }
}
