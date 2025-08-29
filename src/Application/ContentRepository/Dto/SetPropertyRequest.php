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
 * Data Transfer Object for setting a property on a content repository node.
 */
final readonly class SetPropertyRequest
{
    /**
     * Constructor for SetPropertyRequest.
     *
     * @param string                $workspaceId   the identifier of the workspace
     * @param array<string, string> $dimensions    the dimensions for the node context
     * @param string                $nodeId        the identifier of the node
     * @param string                $propertyName  the name of the property to set
     * @param mixed                 $propertyValue the value to assign to the property
     */
    public function __construct(
        public string $workspaceId,
        public array $dimensions,
        public string $nodeId,
        public string $propertyName,
        public mixed $propertyValue,
    ) {
    }
}
