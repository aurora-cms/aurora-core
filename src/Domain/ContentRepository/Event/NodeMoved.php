<?php

namespace Aurora\Domain\ContentRepository\Event;

use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\Event\AbstractEvent;

/**
 * Event representing the movement of a node within the content repository.
 *
 * @property WorkspaceId $workspaceId The workspace where the node resides.
 * @property DimensionSet $dimensionSet The dimension set of the node.
 * @property NodeId $nodeId The ID of the moved node.
 * @property NodeId $oldParentId The ID of the old parent node.
 * @property NodeId $newParentId The ID of the new parent node.
 * @property NodePath $oldPath The previous path of the node.
 * @property NodePath $newPath The new path of the node.
 */
final readonly class NodeMoved extends AbstractEvent
{
    /**
     * NodeMoved constructor.
     *
     * @param WorkspaceId $workspaceId
     * @param DimensionSet $dimensionSet
     * @param NodeId $nodeId
     * @param NodeId $oldParentId
     * @param NodeId $newParentId
     * @param NodePath $oldPath
     * @param NodePath $newPath
     */
    public function __construct(
        public WorkspaceId $workspaceId,
        public DimensionSet $dimensionSet,
        public NodeId $nodeId,
        public NodeId $oldParentId,
        public NodeId $newParentId,
        public NodePath $oldPath,
        public NodePath $newPath,
    )
    {
        parent::__construct();
    }
}
