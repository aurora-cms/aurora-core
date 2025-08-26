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
 * Value object representing a UUID v4 NodeId.
 */
final readonly class NodeId
{
    /**
     * Constructs a NodeId from a string value.
     *
     * @param string $value UUID v4 string
     *
     * @throws \InvalidArgumentException if the value is not a valid UUID v4
     */
    public function __construct(private string $value)
    {
        if (!self::isValid($value)) {
            throw new \InvalidArgumentException('Invalid NodeId (UUID v4 expected): '.$value);
        }
    }

    /**
     * Validates if the given string is a valid UUID v4.
     */
    public static function isValid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }

    /**
     * Returns the NodeId value as string.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Returns the NodeId value as string.
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
