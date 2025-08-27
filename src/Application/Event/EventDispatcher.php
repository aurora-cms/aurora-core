<?php

namespace Aurora\Application\Event;

use Aurora\Domain\Event\DomainEvent;

/**
 * Interface for dispatching domain events.
 */
interface EventDispatcher
{
    /**
     * Dispatches a single domain event.
     *
     * @param DomainEvent $event The event to dispatch.
     */
    public function dispatch(DomainEvent $event): void;

    /**
     * Dispatches multiple domain events.
     *
     * @param iterable<DomainEvent> $events The events to dispatch.
     */
    public function dispatchAll(iterable $events): void;
}
