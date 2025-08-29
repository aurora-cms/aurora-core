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
 * Data Transfer Object representing the response for setting a property on a node.
 */
final readonly class SetPropertyResponse
{
    /**
     * Constructor for SetPropertyResponse.
     *
     * @param string $nodeId        the ID of the node
     * @param string $propertyName  the name of the property
     * @param mixed  $propertyValue the value of the property
     */
    public function __construct(public string $nodeId, public string $propertyName, public mixed $propertyValue)
    {
    }
}
