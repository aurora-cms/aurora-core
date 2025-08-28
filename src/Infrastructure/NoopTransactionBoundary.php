<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure;

use Aurora\Application\Contract\TransactionBoundary;

/**
 * A no-operation implementation of TransactionBoundary.
 * Executes the given operation without any transactional behavior.
 */
class NoopTransactionBoundary implements TransactionBoundary
{
    /**
     * {@inheritDoc}
     */
    public function run(callable $operation): mixed
    {
        return $operation();
    }
}
