<?php

namespace Aurora\Domain\ContentRepository\Event;

use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\Event\AbstractEvent;

/**
 * Event representing the setting of a property on a node.
 *
 * @property WorkspaceId $workspaceId The workspace identifier.
 * @property DimensionSet $dimensionSet The dimension set for the node.
 * @property NodeId $nodeId The node identifier.
 * @property string $name The name of the property being set.
 * @property mixed $value The value assigned to the property.
 */
final readonly class NodePropertySet extends AbstractEvent
{
    /**
     * Constructs a NodePropertySet event.
     *
     * @param WorkspaceId $workspaceId The workspace identifier.
     * @param DimensionSet $dimensionSet The dimension set for the node.
     * @param NodeId $nodeId The node identifier.
     * @param string $name The name of the property being set.
     * @param mixed $value The value assigned to the property.
     */
    public function __construct(
        public WorkspaceId $workspaceId,
        public DimensionSet $dimensionSet,
        public NodeId $nodeId,
        public string $name,
        public mixed $value,
    )
    {
        parent::__construct();
    }
}
