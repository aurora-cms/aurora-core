<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

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
     * @param DomainEvent $event the event to dispatch
     */
    public function dispatch(DomainEvent $event): void;

    /**
     * Dispatches multiple domain events.
     *
     * @param iterable<DomainEvent> $events the events to dispatch
     */
    public function dispatchAll(iterable $events): void;
}
