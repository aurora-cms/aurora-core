<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

use InvalidArgumentException;
use Stringable;

/**
 * Represents a unique identifier for a workspace.
 *
 * @readonly
 */
final readonly class WorkspaceId implements Stringable
{
    /**
     * Constructs a WorkspaceId.
     *
     * @param string $value The workspace identifier.
     * @throws InvalidArgumentException If the value is empty or has an invalid format.
     */
    public function __construct(private string $value)
    {
        if ('' === trim($value)) {
            throw new InvalidArgumentException('WorkspaceId cannot be empty.');
        }

        if (!preg_match('/^[A-Za-z0-9_\-]{2,}$/', $value)) {
            throw new InvalidArgumentException('WorkspaceId format invalid');
        }
    }

    /**
     * Creates a WorkspaceId from a string.
     *
     * @param string $value
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Checks if this WorkspaceId is equal to another.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Returns the string representation of the WorkspaceId.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
