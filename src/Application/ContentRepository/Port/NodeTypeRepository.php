<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\Port;

use Aurora\Domain\ContentRepository\Type\NodeType;

/**
 * Interface NodeTypeRepository.
 *
 * Provides an abstraction for managing and retrieving node types within the content repository.
 */
interface NodeTypeRepository
{
    /**
     * Retrieves a NodeType by its name.
     */
    public function get(string $name): NodeType;

    /**
     * Returns all registered NodeTypes.
     *
     * @return NodeType[]
     */
    public function all(): array;

    /**
     * Checks if a NodeType with the given name exists.
     */
    public function has(string $name): bool;

    /**
     * Registers a new NodeType.
     */
    public function register(NodeType $type): void;
}
