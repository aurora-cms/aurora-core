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
 * Data Transfer Object representing the response for fetching a node by its path.
 *
 * @readonly
 */
final readonly class GetNodeByPathResponse
{
    /**
     * Constructor for GetNodeByPathResponse.
     *
     * @param string $nodeId     the unique identifier of the node
     * @param string $path       the path of the node
     * @param string $nodeType   the type of the node
     * @param array  $properties additional properties of the node
     */
    public function __construct(
        public string $nodeId,
        public string $path,
        public string $nodeType,
        public array $properties = [],
    ) {
    }
}
