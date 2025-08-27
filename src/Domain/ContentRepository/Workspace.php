<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Event\NodeCreated;
use Aurora\Domain\ContentRepository\Event\NodeMoved;
use Aurora\Domain\ContentRepository\Event\NodePropertySet;
use Aurora\Domain\ContentRepository\Event\NodeRemoved;
use Aurora\Domain\ContentRepository\Exception\InvalidMove;
use Aurora\Domain\ContentRepository\Exception\NodeAlreadyExists;
use Aurora\Domain\ContentRepository\Exception\NodeHasChildren;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Exception\RemovingRootNotAllowed;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\Event\DomainEvent;
use InvalidArgumentException;

final class Workspace
{
    /** @var array<string,Node> */
    private array $nodes = [];
    /** @var array<string,array<string>> childId lists keyed by parent id */
    private array $children = [];
    private NodeId $rootId;
    /**
     * @var array<DomainEvent>
     */
    private array $events = [];

    /**
     * Constructs a new instance of the class.
     *
     * @param WorkspaceId $id the unique identifier for the workspace
     * @param DimensionSet $dimensionSet the set of dimensions associated with the workspace
     * @param Node $root the root node of the workspace
     *
     * @throws InvalidArgumentException if the root node does not have a root path
     * @throws InvalidArgumentException if the root node's workspace ID does not match the workspace ID
     * @throws InvalidArgumentException if the root node's dimension set does not match the workspace dimension set
     */
    public function __construct(
        public readonly WorkspaceId  $id,
        public readonly DimensionSet $dimensionSet,
        Node                         $root,
    )
    {
        if (!$root->path->isRoot()) {
            throw new InvalidArgumentException('Root node must have root path');
        }
        if (!$root->workspaceId->equals($this->id)) {
            throw new InvalidArgumentException('Root workspace mismatch');
        }
        if (!$root->dimensionSet->equals($this->dimensionSet)) {
            throw new InvalidArgumentException('Root dimension set mismatch');
        }
        $this->rootId = $root->id;
        $this->nodes[(string)$root->id] = $root;
        $this->children[(string)$root->id] = [];
    }

    /**
     * Initializes a new workspace instance with the given parameters.
     *
     * @param WorkspaceId $id the unique identifier of the workspace
     * @param DimensionSet $dimensions the set of dimensions for the workspace
     * @param NodeId $rootId the identifier of the root node
     * @param NodeType $rootType the type of the root node
     *
     * @return self a new instance of the workspace
     */
    public static function initialize(WorkspaceId $id, DimensionSet $dimensions, NodeId $rootId, NodeType $rootType): self
    {
        $root = new Node($rootId, $id, $dimensions, $rootType, NodePath::root(), []);

        return new self($id, $dimensions, $root);
    }

    /**
     * Retrieves the root node of the structure.
     *
     * @return Node the root node
     */
    public function root(): Node
    {
        return $this->get($this->rootId);
    }

    /**
     * Retrieves a node by its ID.
     *
     * @param NodeId $id the ID of the node to retrieve
     *
     * @return Node the node corresponding to the provided ID
     *
     * @throws NodeNotFound thrown when the node with the given ID does not exist
     */
    public function get(NodeId $id): Node
    {
        $key = (string)$id;
        if (!isset($this->nodes[$key])) {
            throw new NodeNotFound('Node not found: ' . $key);
        }

        return $this->nodes[$key];
    }

    /**
     * Retrieves the children nodes of a given parent node.
     *
     * @param NodeId $parentId the ID of the parent node whose children are to be retrieved
     *
     * @return Node[] an array containing the child nodes of the specified parent node
     */
    public function childrenOf(NodeId $parentId): array
    {
        $result = [];
        foreach ($this->children[(string)$parentId] ?? [] as $childId) {
            $result[] = $this->nodes[$childId];
        }

        return $result;
    }

    /**
     * Creates a new node in the hierarchy.
     *
     * @param NodeId $id the unique identifier of the node to be created
     * @param NodeType $type the type of the node to be created
     * @param array<string, mixed> $properties the properties of the node to validate and assign
     * @param NodeId $parentId the identifier of the parent node under which this node will be created
     * @param string $segment the unique segment name for the node within its siblings
     *
     * @throws NodeAlreadyExists if a node with the same segment name already exists within the siblings or if a node with the same ID already exists
     */
    public function createNode(NodeId $id, NodeType $type, array $properties, NodeId $parentId, string $segment): void
    {
        $parent = $this->get($parentId);

        // Validate uniqueness within siblings(segment)
        foreach ($this->childrenOf($parentId) as $sibling) {
            if ($sibling->path->name() === strtolower($segment)) {
                throw new NodeAlreadyExists('Node already exists: ' . $segment);
            }
        }

        // Validate properties against node type
        $type->validateProperties($properties);

        $path = $parent->path->append($segment);
        $node = new Node($id, $this->id, $this->dimensionSet, $type, $path, $properties);
        $key = (string)$id;
        if (isset($this->nodes[$key])) {
            throw new NodeAlreadyExists('Node already exists: ' . $key);
        }
        $this->nodes[$key] = $node;
        $this->children[$key] = [];
        $this->children[(string)$parentId][] = $key;
        $this->record(new NodeCreated($this->id, $this->dimensionSet, $id, $parentId, strtolower($segment), $path, $type, $properties));
    }

    /**
     * Sets a property on the specified node.
     *
     * @param NodeId $id identifier of the node to update
     * @param string $name name of the property to set
     * @param mixed $value value to assign to the property
     *
     * @throws NodeNotFound if the node with the specified ID does not exist
     */
    public function setProperty(NodeId $id, string $name, mixed $value): void
    {
        $node = $this->get($id);
        $updated = $node->withProperty($name, $value);
        $this->nodes[(string)$id] = $updated;
        $this->record(new NodePropertySet($this->id, $this->dimensionSet, $id, $name, $value));
    }

    /**
     * Moves a node to a new parent within the tree structure.
     *
     * @param NodeId $id the identifier of the node to be moved
     * @param NodeId $newParentId the identifier of the new parent node
     *
     * @throws InvalidMove if the node to be moved is the root node
     * @throws InvalidMove if the new parent node is a descendant of the node being moved
     * @throws InvalidMove if a sibling with the same name already exists at the target location
     */
    public function move(NodeId $id, NodeId $newParentId): void
    {
        if ($id->equals($this->rootId)) {
            throw new InvalidMove('Cannot move root node');
        }
        $node = $this->get($id);
        $newParent = $this->get($newParentId);

        // Prevent moving into own subtree
        if ($this->isDescendant($newParentId, $id)) {
            throw new InvalidMove('Cannot move node into its own subtree');
        }

        // Sibling name conflict at new parent
        $segment = $node->path->name();
        foreach ($this->childrenOf($newParentId) as $sibling) {
            if ($sibling->path->name() === $segment) {
                throw new InvalidMove('Node with same name already exists at target location: ' . $segment);
            }
        }

        // Update path for the node and all descendants
        $oldPath = (string)$node->path;
        $newPath = (string)$newParent->path->append($segment);

        $oldParent = $this->detachFromParent($id);
        $this->children[(string)$newParentId][] = (string)$id;

        foreach ($this->nodes as $k => $n) {
            if (str_starts_with($n->path . '/', $oldPath . '/') || $n->path->equals($node->path)) {
                $suffix = substr((string)$n->path, \strlen($oldPath));
                $this->nodes[$k] = $n->withPath(NodePath::fromString($newPath . $suffix));
            }
        }

        $this->record(new NodeMoved(
            $this->id,
            $this->dimensionSet,
            $id,
            $oldParent ?? $newParentId,
            $newParentId,
            NodePath::fromString($oldPath),
            NodePath::fromString($newPath)
        ));
    }

    /**
     * Removes a node from the structure.
     *
     * @param NodeId $id the ID of the node to be removed
     * @param bool $cascade whether to remove the node and its children recursively
     *
     * @throws RemovingRootNotAllowed thrown when attempting to remove the root node
     * @throws NodeHasChildren        thrown when attempting to remove a node that has children without cascading
     */
    public function remove(NodeId $id, bool $cascade = false): void
    {
        if ($id->equals($this->rootId)) {
            throw new RemovingRootNotAllowed('Cannot remove root node');
        }

        $children = $this->children[(string)$id] ?? [];
        if (!$cascade && !empty($children)) {
            throw new NodeHasChildren('Cannot remove node with children');
        }
        $idsToRemove = $cascade ? $this->collectSubtreeIds($id) : [(string)$id];

        // Detach from parent
        $this->detachFromParent($id);

        $removed = $idsToRemove;
        foreach ($idsToRemove as $key) {
            unset($this->nodes[$key], $this->children[$key]);
        }

        $this->record(new NodeRemoved(
            $this->id,
            $this->dimensionSet,
            $id,
            $cascade,
            $removed
        ));
    }

    /**
     * Detaches a node from its parent by removing its ID from the parent's list of children.
     *
     * @param NodeId $id the ID of the node to detach from its parent
     *
     * @return NodeId|null the ID of the parent node from which the child was detached, or null if the node had no parent
     */
    private function detachFromParent(NodeId $id): ?NodeId
    {
        foreach ($this->children as $parentKey => &$childList) {
            $before = $childList;
            $childList = array_values(array_filter($childList, fn($childId) => $childId !== (string)$id));
            if ($before !== $childList) {
                return NodeId::fromString($parentKey);
            }
        }
        return null;
    }

    /**
     * Recursively collects the IDs of a subtree starting from the given node ID.
     *
     * @param NodeId $id the ID of the node to start collecting IDs from
     *
     * @return string[] the array of IDs within the subtree, including the given node ID
     */
    private function collectSubtreeIds(NodeId $id): array
    {
        $result = [(string)$id];
        foreach ($this->children[(string)$id] ?? [] as $childId) {
            $result = array_merge($result, $this->collectSubtreeIds(NodeId::fromString($childId)));
        }

        return $result;
    }

    /**
     * Determines if a given node is a descendant of another node.
     *
     * This method checks whether the specified candidate node exists within the subtree
     * of the given ancestor node. Recursively verifies through the children of the ancestor
     * node to confirm the descendant relationship.
     *
     * @param NodeId $candidate the node to check as a potential descendant
     * @param NodeId $ancestor the node to check as the potential ancestor
     *
     * @return bool true if the candidate node is a descendant of the ancestor node, false otherwise
     */
    private function isDescendant(NodeId $candidate, NodeId $ancestor): bool
    {
        foreach ($this->children[(string)$ancestor] ?? [] as $direct) {
            if ($direct === (string)$candidate) {
                return true;
            }
            if ($this->isDescendant($candidate, NodeId::fromString($direct))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves all recorded domain events and clears the event list.
     *
     * @return DomainEvent[] an array of domain events that were recorded
     */
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    private function record(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}
