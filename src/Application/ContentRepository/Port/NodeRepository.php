<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\Port;

use Aurora\Domain\ContentRepository\Node;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

interface NodeRepository
{
    /**
     * Persists or updates a node.
     */
    public function save(Node $node, ?NodeId $parentId = null, ?string $segment = null): void;

    /**
     * Removes a node by id. If cascade is true, removes subtree as well.
     */
    public function remove(NodeId $id, bool $cascade = false): void;

    /**
     * Finds a node by id.
     */
    public function get(NodeId $id): Node;

    /**
     * Finds a node by workspace, dimensions and path.
     */
    public function getByPath(WorkspaceId $workspaceId, DimensionSet $dimensions, NodePath $path): Node;

    /**
     * Returns children of a node.
     *
     * @return Node[]
     */
    public function childrenOf(NodeId $parentId): array;
}
