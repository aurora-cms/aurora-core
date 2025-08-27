<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

/**
 * Represents a property with a name and value.
 *
 * @readonly
 */
final readonly class Property
{
    /**
     * Property constructor.
     *
     * @param string $name  The name of the property.
     * @param mixed  $value The value of the property.
     */
    public function __construct(public string $name, public mixed $value)
    {
    }
}
