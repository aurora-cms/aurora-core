<?php

namespace Aurora\Infrastructure\Event;

use Aurora\Application\Event\EventDispatcher;
use Aurora\Domain\Event\DomainEvent;

class SyncEventDispatcher implements EventDispatcher
{
    /** @var array<string, array<int, callable(DomainEvent):void>> */
    private array $listeners = [];

    /**
     * Subscribes a listener to a specific event class.
     *
     * @param string $eventClass The fully qualified class name of the event.
     * @param callable(DomainEvent):void $listener The listener callback to handle the event.
     * @return self
     */
    public function subscribe(string $eventClass, callable $listener): self
    {
        $this->listeners[$eventClass] ??= [];
        $this->listeners[$eventClass][] = $listener;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function dispatch(DomainEvent $event): void
    {
        $fqcn = $event::class;

        foreach($this->listeners[$fqcn] ?? [] as $listener) {
            $listener($event);
        }

        // Notify listeners registered for parent classes or interfaces (including DomainEvent)
        $impl = array_merge(class_implements($event) ?: [], class_parents($event) ?: []);
        foreach($impl as $interfaceOrParent) {
            foreach ($this->listeners[$interfaceOrParent] ?? [] as $listener) {
                $listener($event);
            }
        }

        foreach ($this->listeners['*'] ?? [] as $listener) {
            $listener($event);
        }
    }

    /**
     * @inheritDoc
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
    public function reset(): void {
        $this->listeners = [];
    }
}
