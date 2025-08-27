<?php

namespace Aurora\Domain\ContentRepository\Event;


use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\Event\AbstractEvent;

/**
 * Event representing the creation of a node in the content repository.
 *
 * @property WorkspaceId $workspaceId The workspace where the node is created.
 * @property DimensionSet $dimensionSet The dimension set of the node.
 * @property NodeId $nodeId The unique identifier of the created node.
 * @property NodeId $parentId The unique identifier of the parent node.
 * @property string $segment The segment name of the node.
 * @property NodePath $path The path of the node in the repository.
 * @property NodeType $nodeType The type of the node.
 * @property array $properties The properties assigned to the node.
 */
final readonly class NodeCreated extends AbstractEvent
{
    /**
     * NodeCreated constructor.
     *
     * @param WorkspaceId $workspaceId
     * @param DimensionSet $dimensionSet
     * @param NodeId $nodeId
     * @param NodeId $parentId
     * @param string $segment
     * @param NodePath $path
     * @param NodeType $nodeType
     * @param array $properties
     */
    public function __construct(
        public WorkspaceId $workspaceId,
        public DimensionSet $dimensionSet,
        public NodeId $nodeId,
        public NodeId $parentId,
        public string $segment,
        public NodePath $path,
        public NodeType $nodeType,
        public array $properties,
    )
    {
        parent::__construct();
    }
}
