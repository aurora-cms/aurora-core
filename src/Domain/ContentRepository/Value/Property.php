<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

final readonly class Property
{
    public function __construct(public string $name, public mixed $value)
    {
    }
}
