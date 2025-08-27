<?php

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Exception\InvalidMove;
use Aurora\Domain\ContentRepository\Exception\NodeAlreadyExists;
use Aurora\Domain\ContentRepository\Exception\NodeHasChildren;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Exception\NodePathInvalid;
use Aurora\Domain\ContentRepository\Exception\PropertyValidationFailed;
use Aurora\Domain\ContentRepository\Exception\RemovingRootNotAllowed;
use Aurora\Domain\ContentRepository\Exception\UndefinedProperty;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use PHPUnit\Framework\TestCase;

const UUID = '345bf989-2774-4ac3-b117-c7d0dec40675';
const UUID2 = '345bf989-2774-4ac3-b117-c7d0dec40676';
const UUID3 = '345bf989-2774-4ac3-b117-c7d0dec40677';
const UUID4 = '345bf989-2774-4ac3-b117-c7d0dec40678';

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

    public function testCreateNodeUnderRoot(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'Home'], $ws->root()->id, 'home');
        $children = $ws->childrenOf($ws->root()->id);
        $this->assertCount(1, $children);
        $this->assertSame('/home', (string)$children[0]->path);
        $this->assertSame('Home', $children[0]->properties['title']);
        $this->assertSame(UUID2, (string)$children[0]->id);
    }

    public function testCreateNodeSiblingNameConflict(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'Home'], $ws->root()->id, 'home');
        $this->expectException(NodeAlreadyExists::class);
        $ws->createNode(new NodeId(UUID3), $this->defaultType(), ['title' => 'Home'], $ws->root()->id, 'home');
    }

    public function testCreateNodeParentNotFound(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $this->expectException(NodeNotFound::class);
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'Home'], new NodeId(UUID3), 'home');
    }

    public function testMoveNode(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(new NodeId(UUID3), $this->defaultType(), ['title' => 'B'], $ws->root()->id, 'b');
        $ws->createNode(new NodeId(UUID4), $this->defaultType(), ['title' => 'C'], $ws->root()->id, 'c');

        // move a under b -> /b/a and descendant paths remain consistent
        $ws->move(new NodeId(UUID2), new NodeId(UUID3));
        $childrenB = $ws->childrenOf(new NodeId(UUID3));
        $paths = array_map(fn($n) => (string)$n->path, $childrenB);
        $this->assertContains('/b/a', $paths);;
    }

    public function testMoveNodeCannotMoveRoot(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $this->expectException(InvalidMove::class);
        $ws->move($ws->root()->id, new NodeId(UUID));
    }

    public function testMoveNodeCannotCreateCycle(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(new NodeId(UUID3), $this->defaultType(), ['title' => 'B'], new NodeId(UUID2), 'b');
        $this->expectException(InvalidMove::class);
        // move a under b (its own descendant) - should fail
        $ws->move(new NodeId(UUID2), new NodeId(UUID3));
    }

    public function testRemoveNodeWithChildrenFailsWithoutCascade(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(new NodeId(UUID3), $this->defaultType(), ['title' => 'B'], new NodeId(UUID2), 'b');
        $this->expectException(NodeHasChildren::class);
        // remove a which has child b - should fail
        $ws->remove(new NodeId(UUID2));
    }

    public function testCascadeRemoveNode(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(new NodeId(UUID3), $this->defaultType(), ['title' => 'B'], new NodeId(UUID2), 'b');
        // remove a which has child b - should succeed with cascade
        $ws->remove(new NodeId(UUID2), cascade: true);
        $this->expectException(NodeNotFound::class);
        $ws->get(new NodeId(UUID3));
    }

    public function testRemoveNodeWithoutCascade(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A'], $ws->root()->id, 'a');
        // remove a which has no children - should succeed without cascade
        $ws->remove(new NodeId(UUID2));
        $this->expectException(NodeNotFound::class);
        $ws->get(new NodeId(UUID2));
    }

    public function testRemoveRootNodeFails(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $this->expectException(RemovingRootNotAllowed::class);
        $ws->remove($ws->root()->id, cascade: true);
    }

    public function testSetPropertyValidatesType(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A', 'views' => 10], $ws->root()->id, 'a');
        $ws->setProperty(new NodeId(UUID2), 'title', 'New Title');
        $a = $ws->get(new NodeId(UUID2));
        $this->assertSame('New Title', $a->properties['title']);
    }

    public function testSetPropertyUndefined(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A', 'views' => 10], $ws->root()->id, 'a');
        $this->expectException(UndefinedProperty::class);
        $ws->setProperty(new NodeId(UUID2), 'undefined_property', 'value');
    }

    public function testSetPropertyTypeMismatch(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(),new NodeId(UUID), $this->defaultType());
        $ws->createNode(new NodeId(UUID2), $this->defaultType(), ['title' => 'A', 'views' => 10], $ws->root()->id, 'a');
        $this->expectException(PropertyValidationFailed::class);
        $ws->setProperty(new NodeId(UUID2), 'views', 'not_an_int');
    }

    public function testNodePathValidation(): void
    {
        $this->expectException(NodePathInvalid::class);
        NodePath::fromString('relative/path');
    }
}
