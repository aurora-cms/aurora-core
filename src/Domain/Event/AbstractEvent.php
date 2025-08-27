<?php

namespace Aurora\Domain\Event;

use DateTimeImmutable;

/**
 * Abstract base class for domain events.
 * Stores the occurrence time of the event.
 */
abstract readonly class AbstractEvent implements DomainEvent
{
    /**
     * The date and time when the event occurred.
     */
    private DateTimeImmutable $occurredOn;

    /**
     * Initializes the event with the current date and time.
     */
    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
    }

    /**
     * Returns the date and time when the event occurred.
     *
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
