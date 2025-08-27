<?php

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Event\NodeCreated;
use Aurora\Domain\ContentRepository\Event\NodeMoved;
use Aurora\Domain\ContentRepository\Event\NodePropertySet;
use Aurora\Domain\ContentRepository\Event\NodeRemoved;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use PHPUnit\Framework\TestCase;

final class DomainEventsTest extends TestCase
{
    private function defaultType(): NodeType
    {
        return new NodeType('document', [
            new PropertyDefinition('title', PropertyType::STRING, nullable: false),
        ]);
    }

    public function testCreateEmitsNodeCreated(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(), NodeId::fromString('node-1'), $this->defaultType());
        $ws->createNode(NodeId::fromString('node-2'), $this->defaultType(), ['title' => 'Hello'], $ws->root()->id, 'hello');
        $events = $ws->pullEvents();
        $this->assertNotEmpty($events);
        $this->assertInstanceOf(NodeCreated::class, $events[0]);
        $this->assertInstanceOf(\DateTimeImmutable::class, $events[0]->occurredOn());
    }

    public function testMoveEmitsNodeMoved(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(), NodeId::fromString('rootnode-1'), $this->defaultType());
        $ws->createNode(NodeId::fromString('childnode-a'), $this->defaultType(), ['title' => 'A'], $ws->root()->id, 'a');
        $ws->createNode(NodeId::fromString('childnode-b'), $this->defaultType(), ['title' => 'B'], $ws->root()->id, 'b');
        $ws->move(NodeId::fromString('childnode-a'), NodeId::fromString('childnode-b'));
        $events = $ws->pullEvents();
        $this->assertTrue(array_reduce($events, fn($carry, $e) => $carry || $e instanceof NodeMoved, false));
        $this->assertInstanceOf(\DateTimeImmutable::class, $events[0]->occurredOn());
    }

    public function testRemoveEmitsNodeRemoved(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(), new NodeId('rootnode-1'), $this->defaultType());
        $ws->createNode(new NodeId('childnode-a'), $this->defaultType(), ['title' => 'A'], new NodeId('rootnode-1'), 'a');
        $ws->remove(new NodeId('childnode-a'), cascade:true);
        $events = $ws->pullEvents();
        $this->assertTrue(array_reduce($events, fn($carry,$e)=>$carry || $e instanceof NodeRemoved, false));
        $this->assertInstanceOf(\DateTimeImmutable::class, $events[0]->occurredOn());
    }

    public function testSetPropertyEmitsNodePropertySet(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(), new NodeId('rootnode-1'), $this->defaultType());
        $ws->createNode(new NodeId('childnode-a'), $this->defaultType(), ['title' => 'A'], new NodeId('rootnode-1'), 'a');
        $ws->setProperty(new NodeId('childnode-a'), 'title', 'B');
        $events = $ws->pullEvents();
        $this->assertInstanceOf(NodePropertySet::class, end($events));
        $this->assertInstanceOf(\DateTimeImmutable::class, $events[0]->occurredOn());
    }

    public function testPullEventsResetsQueue(): void
    {
        $ws = Workspace::initialize(new WorkspaceId('draft'), DimensionSet::empty(), new NodeId('rootnode-1'), $this->defaultType());
        $ws->createNode(new NodeId('childnode-a'), $this->defaultType(), ['title' => 'A'], new NodeId('rootnode-1'), 'a');
        $this->assertNotEmpty($ws->pullEvents());
        $this->assertSame([], $ws->pullEvents());
    }
}
