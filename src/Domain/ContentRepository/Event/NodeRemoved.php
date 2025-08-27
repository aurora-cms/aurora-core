<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Event;

use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\Event\AbstractEvent;

/**
 * Event representing the removal of a node from the content repository.
 *
 * This event is triggered when a node is removed, optionally cascading the removal
 * to child nodes. It contains information about the workspace, dimension set,
 * node identifier, cascade flag, and the list of removed node IDs.
 */
final readonly class NodeRemoved extends AbstractEvent
{
    /**
     * Constructs a NodeRemoved event.
     *
     * @param WorkspaceId   $workspaceId    the workspace where the node was removed
     * @param DimensionSet  $dimensionSet   the dimension set of the node
     * @param NodeId        $nodeId         the identifier of the removed node
     * @param bool          $cascade        whether the removal was cascaded to child nodes
     * @param array<string> $removedNodeIds list of all removed node IDs
     */
    public function __construct(
        public WorkspaceId $workspaceId,
        public DimensionSet $dimensionSet,
        public NodeId $nodeId,
        public bool $cascade,
        public array $removedNodeIds,
    ) {
        parent::__construct();
    }
}
