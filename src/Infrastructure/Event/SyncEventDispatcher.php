<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Event;

use Aurora\Application\Event\EventDispatcher;
use Aurora\Domain\Event\DomainEvent;

class SyncEventDispatcher implements EventDispatcher
{
    /** @var array<class-string<DomainEvent>, list<callable>> */
    private array $listeners = [];

    /**
     * Subscribes a listener to a specific event class.
     *
     * @template T of DomainEvent
     *
     * @param class-string<T>  $eventClass the fully qualified class name of the event
     * @param callable(T):void $listener   the listener callback to handle the event
     */
    public function subscribe(string $eventClass, callable $listener): self
    {
        $this->listeners[$eventClass] ??= [];
        $this->listeners[$eventClass][] = $listener;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(DomainEvent $event): void
    {
        $fqcn = $event::class;

        foreach ($this->listeners[$fqcn] ?? [] as $listener) {
            $listener($event);
        }

        // Notify listeners registered for parent classes or interfaces (including DomainEvent)
        $impl = array_merge(class_implements($event) ?: [], class_parents($event) ?: []);
        foreach ($impl as $interfaceOrParent) {
            foreach ($this->listeners[$interfaceOrParent] ?? [] as $listener) {
                $listener($event);
            }
        }

        foreach ($this->listeners['*'] ?? [] as $listener) {
            $listener($event);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param iterable<DomainEvent> $events an iterable collection of events to dispatch
     */
    public function dispatchAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }

    /**
     * Resets the dispatcher by removing all registered listeners.
     */
    public function reset(): void
    {
        $this->listeners = [];
    }
}
