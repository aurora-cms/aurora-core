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

final readonly class Node
{
    /**
     * @param array<string, mixed> $properties
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

    public function withProperty(string $name, mixed $value): self
    {
        $this->type->definition($name)->validate($value);
        $props = $this->properties;
        $props[$name] = $value;

        return new self($this->id, $this->workspaceId, $this->dimensionSet, $this->type, $this->path, $props);
    }

    public function withPath(NodePath $path): self
    {
        return new self($this->id, $this->workspaceId, $this->dimensionSet, $this->type, $path, $this->properties);
    }
}
