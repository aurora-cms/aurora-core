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
 * Data Transfer Object for deleting a node in the content repository.
 */
final readonly class DeleteNodeRequest
{
    /**
     * @param string                $workspaceId the ID of the workspace
     * @param array<string, string> $dimensions  the dimensions for the node
     * @param string                $nodeId      the ID of the node to delete
     * @param bool                  $cascade     whether to delete child nodes recursively
     */
    public function __construct(
        public string $workspaceId,
        public array $dimensions,
        public string $nodeId,
        public bool $cascade = false,
    ) {
    }
}
