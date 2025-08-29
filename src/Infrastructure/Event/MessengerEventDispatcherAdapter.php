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
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerEventDispatcherAdapter implements EventDispatcher
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ExceptionInterface
     */
    public function dispatch(DomainEvent $event): void
    {
        $this->bus->dispatch($event);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ExceptionInterface
     */
    public function dispatchAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
