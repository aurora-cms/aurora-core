<?php

namespace Aurora\Tests\Infrastructure\Persistence\ContentRepository;

use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryWorkspaceRepository;
use PHPUnit\Framework\TestCase;

class InMemoryWorkspaceRepositoryTest extends TestCase
{

    public function testSaveAndGetWorkspace()
    {
        $repo = new InMemoryWorkspaceRepository();
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('root-1'), new NodeType('root'));
        $repo->save($ws);

        $this->assertTrue($repo->exists(WorkspaceId::fromString('draft'), DimensionSet::empty()));
        $loaded = $repo->get(WorkspaceId::fromString('draft'), DimensionSet::empty());
        $this->assertSame($ws->root()->id, $loaded->root()->id);
    }
}
