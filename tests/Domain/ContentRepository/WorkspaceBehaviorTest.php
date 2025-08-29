<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use PHPUnit\Framework\TestCase;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Exception\NodeAlreadyExists;
use Aurora\Domain\ContentRepository\Exception\PropertyValidationFailed;

final class WorkspaceBehaviorTest extends TestCase
{
    private function docType(): NodeType
    {
        return new NodeType('document', [new PropertyDefinition('title', PropertyType::STRING)]);
    }

    public function testChildrenOfReturnsAllChildren(): void
    {
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('rootnode'), $this->docType());
        $ws->createNode(NodeId::fromString('childnode-c1'), $this->docType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(NodeId::fromString('childnode-c2'), $this->docType(), ['title' => 'B'], $ws->root()->id, 'b');
        $children = $ws->childrenOf($ws->root()->id);
        $this->assertCount(2, $children);
    }

    public function testSiblingNameConflictIsCaseInsensitive(): void
    {
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('rootnode'), $this->docType());
        $ws->createNode(NodeId::fromString('childnode-c1'), $this->docType(), ['title' => 'A'], $ws->root()->id, 'Home');
        try {
            $ws->createNode(NodeId::fromString('childnode-c2'), $this->docType(), ['title' => 'B'], $ws->root()->id, 'HOME');
            $this->fail('Expected NodeAlreadyExists');
        } catch (NodeAlreadyExists $e) {
            $this->assertSame('Node already exists: HOME', $e->getMessage());
        }
    }

    public function testMoveUpdatesDescendantPaths(): void
    {
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('rootnode'), $this->docType());
        $ws->createNode(NodeId::fromString('childnode-a'), $this->docType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(NodeId::fromString('childnode-a1'), $this->docType(), ['title' => 'A1'], NodeId::fromString('childnode-a'), 'a1');
        $ws->createNode(NodeId::fromString('childnode-b'), $this->docType(), ['title' => 'B'], $ws->root()->id, 'b');
        // Add sibling whose name shares prefix with 'a' to catch bad prefix matching
        $ws->createNode(NodeId::fromString('childnode-ab'), $this->docType(), ['title' => 'AB'], $ws->root()->id, 'ab');

        $ws->move(NodeId::fromString('childnode-a'), NodeId::fromString('childnode-b'));
        $this->assertSame('/b/a/a1', (string) $ws->get(NodeId::fromString('childnode-a1'))->path);
    }

    public function testCreateNodeRejectsInvalidPropertyValue(): void
    {
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('rootnode'), $this->docType());
        $this->expectException(PropertyValidationFailed::class);
        $ws->createNode(NodeId::fromString('childnode-x'), $this->docType(), ['title' => 123], $ws->root()->id, 'x');
    }

    public function testMoveSiblingNameConflictThrows(): void
    {
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('rootnode'), $this->docType());
        $ws->createNode(NodeId::fromString('childnode-a'), $this->docType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(NodeId::fromString('childnode-b'), $this->docType(), ['title' => 'B'], $ws->root()->id, 'b');
        $ws->createNode(NodeId::fromString('childnode-c'), $this->docType(), ['title' => 'BA'], NodeId::fromString('childnode-b'), 'a');
        try {
            $ws->move(NodeId::fromString('childnode-a'), NodeId::fromString('childnode-b'));
            $this->fail('Expected InvalidMove');
        } catch (\Aurora\Domain\ContentRepository\Exception\InvalidMove $e) {
            $this->assertSame('Node with same name already exists at target location: a', $e->getMessage());
        }

    }

    public function testMoveUpdatesPathIndex(): void
    {
        $ws = Workspace::initialize(WorkspaceId::fromString('draft'), DimensionSet::empty(), NodeId::fromString('rootnode'), $this->docType());
        $ws->createNode(NodeId::fromString('childnode-a'), $this->docType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(NodeId::fromString('childnode-a1'), $this->docType(), ['title' => 'A1'], NodeId::fromString('childnode-a'), 'a1');
        $ws->createNode(NodeId::fromString('childnode-b'), $this->docType(), ['title' => 'B'], $ws->root()->id, 'b');

        // After move, old path lookups should fail, new should succeed
        $ws->move(NodeId::fromString('childnode-a'), NodeId::fromString('childnode-b'));
        try {
            $ws->getByPath(NodePath::fromString('/a'));
            $this->fail('Expected NodeNotFound at old path');
        } catch (NodeNotFound $e) {
            $this->assertSame('Node not found at path: /a', $e->getMessage());
        }
        $this->assertSame('childnode-a', (string) $ws->getByPath(NodePath::fromString('/b/a'))->id);
        $this->assertSame('childnode-a1', (string) $ws->getByPath(NodePath::fromString('/b/a/a1'))->id);
        $this->assertSame('childnode-b', (string) $ws->getByPath(NodePath::fromString('/b'))->id);
        // Old parent should no longer list 'childnode-a' as child
        $rootChildren = $ws->childrenOf($ws->root()->id);
        $this->assertFalse(in_array('childnode-a', array_map(fn($n) => (string) $n->id, $rootChildren), true));
    }
}
