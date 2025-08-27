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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class SymfonyEventDispatcherAdapter implements EventDispatcher
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
