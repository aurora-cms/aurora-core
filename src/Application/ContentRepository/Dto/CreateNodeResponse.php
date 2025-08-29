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
 * Response DTO for node creation.
 *
 * @readonly
 */
final class CreateNodeResponse
{
    /**
     * Create a new response for a created node.
     *
     * @param string $nodeId the unique identifier of the created node
     * @param string $path   the path of the created node
     */
    public function __construct(public string $nodeId, public string $path)
    {
    }
}
