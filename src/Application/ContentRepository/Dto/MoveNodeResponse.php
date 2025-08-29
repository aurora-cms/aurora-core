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
 * DTO representing the response for moving a node in the content repository.
 *
 * @readonly
 */
final readonly class MoveNodeResponse
{
    /**
     * MoveNodeResponse constructor.
     *
     * @param string $nodeId  the ID of the moved node
     * @param string $newPath the new path of the node after moving
     */
    public function __construct(
        public string $nodeId,
        public string $newPath,
    ) {
    }
}
