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
 * Data Transfer Object for creating a new node in the content repository.
 *
 * @readonly
 */
final readonly class CreateNodeRequest
{
    /**
     * Constructor for CreateNodeRequest.
     *
     * @param string                $workspaceId the ID of the workspace where the node will be created
     * @param array<string, string> $dimensions  The dimensions for the node (e.g., language, region).
     * @param string                $parentId    the ID of the parent node
     * @param string                $newNodeId   the unique ID for the new node
     * @param string                $segment     the segment identifier for the node
     * @param string                $nodeType    the type of the node to be created
     * @param array                 $properties  additional properties for the node
     */
    public function __construct(
        public string $workspaceId,
        public array $dimensions,
        public string $parentId,
        public string $newNodeId,
        public string $segment,
        public string $nodeType,
        public array $properties = [],
    ) {
    }
}
