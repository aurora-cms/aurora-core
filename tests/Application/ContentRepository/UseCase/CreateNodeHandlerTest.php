<?php

namespace Aurora\Tests\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\CreateNodeRequest;
use Aurora\Application\ContentRepository\UseCase\CreateNodeHandler;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\NoopTransactionBoundary;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryWorkspaceRepository;
use PHPUnit\Framework\TestCase;

final class CreateNodeHandlerTest extends TestCase
{
    public function testCreateNodeHappyPath(): void
    {
        $repo = new InMemoryWorkspaceRepository();
        $types = new InMemoryNodeTypeRepository([new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING)])]);
        $tx = new NoopTransactionBoundary();
        $ws = Workspace::initialize(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('rootnode-1'),
            $types->get('document'),
        );
        $repo->save($ws);
        $handler = new CreateNodeHandler($repo, $types, $tx);
        $r = $handler(new CreateNodeRequest(
            'draft',
            [],
            'rootnode-1',
            'childnode-1',
            'home',
            'document',
            ['title' => 'Home'],
        ));
        $this->assertSame('childnode-1', $r->nodeId);
        $this->assertSame('/home', $r->path);
    }

    public function testCreateNodeWithInvalidParent(): void
    {
        $repo = new InMemoryWorkspaceRepository();
        $types = new InMemoryNodeTypeRepository([new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING)])]);
        $tx = new NoopTransactionBoundary();
        $ws = Workspace::initialize(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('rootnode-1'),
            $types->get('document'),
        );
        $repo->save($ws);
        $handler = new CreateNodeHandler($repo, $types, $tx);
        $this->expectException(NodeNotFound::class);
        $handler(new CreateNodeRequest(
            'draft',
            [],
            'nonexisting-parent',
            'childnode-1',
            'home',
            'document',
            ['title' => 'Home'],
        ));
    }
}
