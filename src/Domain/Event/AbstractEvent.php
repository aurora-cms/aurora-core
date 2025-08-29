<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\Event;

/**
 * Abstract base class for domain events.
 * Stores the occurrence time of the event.
 */
abstract readonly class AbstractEvent implements DomainEvent
{
    /**
     * The date and time when the event occurred.
     */
    private \DateTimeImmutable $occurredOn;

    /**
     * Initializes the event with the current date and time.
     */
    public function __construct()
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    /**
     * Returns the date and time when the event occurred.
     */
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
