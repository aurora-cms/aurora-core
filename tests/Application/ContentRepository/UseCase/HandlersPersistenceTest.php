<?php

declare(strict_types=1);

namespace Aurora\Tests\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\DeleteNodeRequest;
use Aurora\Application\ContentRepository\Dto\MoveNodeRequest;
use Aurora\Application\ContentRepository\Dto\SetPropertyRequest;
use Aurora\Application\ContentRepository\UseCase\DeleteNodeHandler;
use Aurora\Application\ContentRepository\UseCase\MoveNodeHandler;
use Aurora\Application\ContentRepository\UseCase\SetPropertyHandler;
use Aurora\Application\Contract\TransactionBoundary;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\NoopTransactionBoundary;
use Aurora\Tests\Support\SpyWorkspaceRepository;
use PHPUnit\Framework\TestCase;

final class HandlersPersistenceTest extends TestCase
{
    private SpyWorkspaceRepository $repo;
    private Workspace $ws;
    private TransactionBoundary $tx;

    protected function setUp(): void
    {
        $this->repo = new SpyWorkspaceRepository();
        $this->tx = new NoopTransactionBoundary();
        $this->ws = Workspace::initialize(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('rootnode-1'),
            new NodeType('root')
        );
        $this->repo->save($this->ws);
    }

    public function testDeleteCallsRemoveAndSave(): void
    {
        // Arrange: create a node
        $this->ws->createNode(NodeId::fromString('to-delete'), new NodeType('document'), [], NodeId::fromString('rootnode-1'), 'd');
        $this->repo->save($this->ws);

        // Act
        $handler = new DeleteNodeHandler($this->repo, $this->tx);
        $before = $this->repo->saveCount;
        $handler(new DeleteNodeRequest('draft', [], 'to-delete', false));

        // Assert save was called (kills repo->save removal)
        self::assertGreaterThan($before, $this->repo->saveCount);

        // Assert node no longer exists (kills ws->remove removal) and message contains id and phrase
        try {
            $this->repo->get(WorkspaceId::fromString('draft'), DimensionSet::empty())->get(NodeId::fromString('to-delete'));
            $this->fail('Expected NodeNotFound');
        } catch (NodeNotFound $e) {
            $this->assertSame('Node not found: to-delete', $e->getMessage());
        }
    }

    public function testMoveCallsSaveAndUpdatesPath(): void
    {
        $doc = new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING)]);
        $this->ws->createNode(NodeId::fromString('childnode-a'), $doc, ['title' => 'A'], NodeId::fromString('rootnode-1'), 'a');
        $this->ws->createNode(NodeId::fromString('childnode-b'), $doc, ['title' => 'B'], NodeId::fromString('rootnode-1'), 'b');
        $this->repo->save($this->ws);

        $handler = new MoveNodeHandler($this->repo, $this->tx);
        $before = $this->repo->saveCount;
        $handler(new MoveNodeRequest('draft', [], 'childnode-a', 'childnode-b'));

        $ws = $this->repo->get(WorkspaceId::fromString('draft'), DimensionSet::empty());
        $this->assertSame('/b/a', (string) $ws->get(NodeId::fromString('childnode-a'))->path);
        self::assertGreaterThan($before, $this->repo->saveCount);
    }

    public function testSetPropertyCallsSetAndSave(): void
    {
        $doc = new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING)]);
        $this->ws->createNode(NodeId::fromString('childnode-n1'), $doc, ['title' => 'Old'], NodeId::fromString('rootnode-1'), 'childnode-n1');
        $this->repo->save($this->ws);

        $handler = new SetPropertyHandler($this->repo, $this->tx);
        $before = $this->repo->saveCount;
        $handler(new SetPropertyRequest('draft', [], 'childnode-n1', 'title', 'New'));

        $ws = $this->repo->get(WorkspaceId::fromString('draft'), DimensionSet::empty());
        $this->assertSame('New', $ws->get(NodeId::fromString('childnode-n1'))->properties['title']);
        self::assertGreaterThan($before, $this->repo->saveCount);
    }
}
