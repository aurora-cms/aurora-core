<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Subscriber\ContentRepository;

use Aurora\Domain\ContentRepository\Event\NodeCreated;
use Aurora\Domain\ContentRepository\Event\NodeMoved;
use Aurora\Domain\ContentRepository\Event\NodePropertySet;
use Aurora\Domain\ContentRepository\Event\NodeRemoved;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function sprintf;

final readonly class NodeEventsLogSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodeCreated::class => 'onNodeCreated',
            NodeMoved::class => 'onNodeMoved',
            NodeRemoved::class => 'onNodeRemoved',
            NodePropertySet::class  => 'onNodePropertySet',
        ];
    }

    public function onNodeCreated(NodeCreated $e): void
    {
        $this->logger->info(sprintf(
            'Node created: %s (path: %s, type: %s)',
            $e->nodeId,
            $e->path,
            $e->nodeType
        ));
    }

    public function onNodeMoved(NodeMoved $e): void
    {
        $this->logger->info(sprintf(
            'Node moved: %s from %s to %s',
            $e->nodeId,
            $e->oldPath,
            $e->newPath
        ));
    }

    public function onNodePropertySet(NodePropertySet $e): void
    {

        $this->logger->info(sprintf(
            'Node property set: %s, property %s: %s',
            $e->nodeId,
            $e->name,
            json_encode($e->value)
        ));
    }

    public function onNodeRemoved(NodeRemoved $e): void
    {
        if ($e->cascade) {
            $this->logger->info(sprintf(
                'Node removed (cascade): %s (others: %s)',
                $e->nodeId,
                json_encode($e->removedNodeIds),
            ));

            return;
        }
        $this->logger->info(sprintf(
            'Node removed: %s',
            $e->nodeId,
        ));
    }
}
