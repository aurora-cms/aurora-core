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
 * Data Transfer Object for moving a node within a content repository.
 *
 * @readonly
 */
final readonly class MoveNodeRequest
{
    /**
     * MoveNodeRequest constructor.
     *
     * @param string                $workspaceId the ID of the workspace
     * @param array<string, string> $dimensions  the dimensions associated with the node
     * @param string                $nodeId      the ID of the node to move
     * @param string                $newParentId the ID of the new parent node
     */
    public function __construct(
        public string $workspaceId,
        public array $dimensions,
        public string $nodeId,
        public string $newParentId,
    ) {
    }
}
