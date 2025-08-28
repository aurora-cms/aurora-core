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
 * Data Transfer Object for retrieving a node by its path in the content repository.
 *
 * @readonly
 */
final readonly class GetNodeByPathRequest
{
    /**
     * @param string                $workspaceId the ID of the workspace
     * @param array<string, string> $dimensions  The dimensions for the node (e.g., language, region).
     * @param string                $path        the path of the node to retrieve
     */
    public function __construct(
        public string $workspaceId,
        public array $dimensions,
        public string $path,
    ) {
    }
}
