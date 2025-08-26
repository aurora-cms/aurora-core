<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Shared\Kernel;

interface DomainEvent
{
    public function getOccurredOn(): \DateTimeImmutable;
}
