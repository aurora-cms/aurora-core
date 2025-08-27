<?php

namespace Aurora\Tests\Infrastructure\Event;

use Aurora\Domain\ContentRepository\Event\NodeCreated;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\Event\DomainEvent;
use Aurora\Infrastructure\Event\SyncEventDispatcher;
use PHPUnit\Framework\TestCase;

final class SyncEventDispatcherTest extends TestCase
{
    public function testDispatchToSubscribedListener(): void
    {
        $bus = new SyncEventDispatcher();
        /** @var string[] $received */
        $received = [];
        $bus->subscribe(NodeCreated::class,
            function (NodeCreated $e) use (&$received) {
                $received[] = $e->nodeId;
            }
        );

        $bus->dispatch(new NodeCreated(
            WorkspaceId::fromString('draft'),
            DimensionSet::empty(),
            NodeId::fromString('testnode-1'),
            NodeId::fromString('rootnode-1'),
            'home',
            NodePath::fromString('/home'),
            new NodeType('doc'),
            ['title' => 'Home'],
        ));

        $this->assertCount(1, $received);
        $this->assertEquals('testnode-1', (string)$received[0]);
    }

    public function testDispatchAllMaintainsOrder(): void
    {
        $bus = new SyncEventDispatcher();
        $order = [];
        $bus->subscribe(NodeCreated::class,
            function (NodeCreated $e) use (&$order): void {
                $order[] = (string)$e->nodeId;
            }
        );

        $bus->dispatchAll([
                new NodeCreated(
                    WorkspaceId::fromString('draft'),
                    DimensionSet::empty(),
                    NodeId::fromString('testnode-1'),
                    NodeId::fromString('rootnode-1'),
                    'home',
                    NodePath::fromString('/home'),
                    new NodeType('doc'),
                    ['title' => 'Home'],
                ),
                new NodeCreated(
                    WorkspaceId::fromString('draft'),
                    DimensionSet::empty(),
                    NodeId::fromString('testnode-2'),
                    NodeId::fromString('rootnode-1'),
                    'about',
                    NodePath::fromString('/about'),
                    new NodeType('doc'),
                    ['title' => 'About'],
                ),
            ]
        );

        $this->assertSame(['testnode-1', 'testnode-2'], $order);
    }

    public function testWildcardSubscriberReceivesAll(): void
    {
        $bus = new SyncEventDispatcher();
        $count = 0;
        $bus->subscribe(DomainEvent::class,
            function ($e) use (&$count): void {
                $count++;
            }
        );

        $bus->dispatchAll([
                new NodeCreated(
                    WorkspaceId::fromString('draft'),
                    DimensionSet::empty(),
                    NodeId::fromString('testnode-1'),
                    NodeId::fromString('rootnode-1'),
                    'home',
                    NodePath::fromString('/home'),
                    new NodeType('doc'),
                    ['title' => 'Home'],
                ),
                new NodeCreated(
                    WorkspaceId::fromString('draft'),
                    DimensionSet::empty(),
                    NodeId::fromString('testnode-2'),
                    NodeId::fromString('rootnode-1'),
                    'about',
                    NodePath::fromString('/about'),
                    new NodeType('doc'),
                    ['title' => 'About'],
                ),
            ]
        );

        $this->assertSame(2, $count);
    }
}
