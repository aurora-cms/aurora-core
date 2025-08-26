<?php

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use PHPUnit\Framework\TestCase;

const UUID = '345bf989-2774-4ac3-b117-c7d0dec40675';
final class WorkSpaceTest extends TestCase
{
    private function defaultType(): NodeType
    {
        return new NodeType('document', [
            new PropertyDefinition('title', PropertyType::STRING, nullable: false),
            new PropertyDefinition('views', PropertyType::INT, nullable: false),
            new PropertyDefinition('tags', PropertyType::STRING, nullable: true, multiple: true),
        ]);
    }

    public function testInitializeWorkspaceWithRoot(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(), new NodeId(UUID), $this->defaultType());
        $root = $ws->root();
        $this->assertSame('/', (string)$root->path);
        $this->assertSame(UUID, (string)$root->id);
        $this->assertSame('draft', (string)$root->workspaceId);
    }
}
