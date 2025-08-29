<?php

namespace Aurora\Tests\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\SetPropertyRequest;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\ContentRepository\UseCase\SetPropertyHandler;
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

class SetPropertyHandlerTest extends TestCase
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

    public function testSetProperty(): void
    {
        try {
            $this->ws->createNode(
                NodeId::fromString('parentnode-1'),
                new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING)]),
                ['title' => 'Old Title'],
                NodeId::fromString('rootnode-1'),
                'node-1'
            );
        } catch (Exception $e) {
            $this->fail('Exception should not be thrown: ' . $e->getMessage());
        }
        $this->repo->save($this->ws);

        $h = new SetPropertyHandler($this->repo, $this->tx);
        $r = $h(new SetPropertyRequest($this->ws->id, [], 'parentnode-1', 'title', 'New Title'));
        $this->assertEquals('parentnode-1', $r->nodeId);
        $this->assertEquals('title', $r->propertyName);
        $this->assertEquals('New Title', $r->propertyValue);
    }
}
