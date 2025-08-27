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
 * Value object representing a UUID/ULID NodeId.
 *
 * This class encapsulates a string value that represents a node identifier,
 * typically a UUID v4 or ULID-like string. It ensures the value is valid
 * and provides utility methods for comparison and string conversion.
 */
final readonly class NodeId implements \Stringable
{
    /**
     * Constructs a NodeId from a string value.
     *
     * @param string $value UUID v4 string
     *
     * @throws \InvalidArgumentException if the value is not a valid UUID v4 or ULID-like string
     */
    public function __construct(private string $value)
    {
        $v = trim($value);
        if ('' === $v) {
            throw new \InvalidArgumentException('NodeId cannot be empty.');
        }

        // allow UUID/ULID-like ids
        if (!preg_match('/^[A-Za-z0-9\-]{6,}$/', $v)) {
            throw new \InvalidArgumentException('NodeId format invalid');
        }
    }

    /**
     * Creates a NodeId instance from a string.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Checks if this NodeId is equal to another NodeId.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Returns the string representation of the NodeId.
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
