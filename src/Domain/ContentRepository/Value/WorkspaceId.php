<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

final readonly class WorkspaceId
{
    public function __construct(private string $value)
    {
        if (!self::isValid($value)) {
            throw new \InvalidArgumentException('Invalid workspace Id slug.');
        }
    }

    public static function isValid(string $workspaceId): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_\-]{2,63}$/', $workspaceId);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
