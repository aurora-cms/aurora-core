<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

/**
 * Represents a content repository node.
 *
 * @readonly
 */
final readonly class Node
{
    /**
     * Constructs a new Node instance.
     *
     * @param NodeId               $id           unique identifier for the node
     * @param WorkspaceId          $workspaceId  workspace identifier
     * @param DimensionSet         $dimensionSet set of dimensions for the node
     * @param NodeType             $type         type definition of the node
     * @param NodePath             $path         path of the node in the repository
     * @param array<string, mixed> $properties   custom properties for the node
     */
    public function __construct(
        public NodeId $id,
        public WorkspaceId $workspaceId,
        public DimensionSet $dimensionSet,
        public NodeType $type,
        public NodePath $path,
        public array $properties = [],
    ) {
    }

    /**
     * Returns a new Node instance with the given property set.
     * Validates the property value against the node type definition.
     *
     * @param string $name  property name
     * @param mixed  $value property value
     */
    public function withProperty(string $name, mixed $value): self
    {
        $this->type->definition($name)->validate($value);
        $props = $this->properties;
        $props[$name] = $value;

        return new self($this->id, $this->workspaceId, $this->dimensionSet, $this->type, $this->path, $props);
    }

    /**
     * Returns a new Node instance with the given path.
     *
     * @param NodePath $path new path for the node
     */
    public function withPath(NodePath $path): self
    {
        return new self($this->id, $this->workspaceId, $this->dimensionSet, $this->type, $path, $this->properties);
    }
}
