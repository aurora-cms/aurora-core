<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\Contract;

/**
 * Interface for defining a transaction boundary.
 * Implementations should ensure that the provided callback
 * is executed within a transactional context.
 */
interface TransactionBoundary
{
    /**
     * Executes the given callback within a transaction boundary.
     *
     * @template T the return type of the callable
     * @param callable(): T $operation the operation to execute within the transaction
     *
     * @return T the result of the callback execution
     */
    public function run(callable $operation);
}
