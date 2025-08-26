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
final readonly class NodeId implements \Stringable
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
        $v = trim($value);
        if ('' === $v) {
            throw new \InvalidArgumentException('NodeId cannot be empty.');
        }

        if (!preg_match('/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/', $v)) {
            throw new \InvalidArgumentException(\sprintf('Invalid NodeId format: "%s". Expected UUID v4 format.', $value));
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
