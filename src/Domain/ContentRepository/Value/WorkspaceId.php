<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

final readonly class WorkspaceId implements \Stringable
{
    public function __construct(private string $value)
    {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('WorkspaceId cannot be empty.');
        }

        if (!preg_match('/^[A-Za-z0-9_\-]{2,}$/', $value)) {
            throw new \InvalidArgumentException('WorkspaceId format invalid');
        }
    }

    public function fromString(string $value): self
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
