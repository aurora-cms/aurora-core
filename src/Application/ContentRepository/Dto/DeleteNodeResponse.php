<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\Dto;

/**
 * DTO representing the response for a node deletion operation.
 * Contains information about the requested node deletion, cascade option,
 * and the IDs of nodes actually deleted.
 */
final readonly class DeleteNodeResponse
{
    /**
     * @param string   $nodeId         the ID of the node that was requested to be deleted
     * @param bool     $cascade        indicates whether the deletion was performed with cascading
     * @param string[] $deletedNodeIds an array of IDs of nodes that were actually deleted as a result of the operation
     */
    public function __construct(
        public string $nodeId,
        public bool $cascade,
        public array $deletedNodeIds = [],
    ) {
    }
}
