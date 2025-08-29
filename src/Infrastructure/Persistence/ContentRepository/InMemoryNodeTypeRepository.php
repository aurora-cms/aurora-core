<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Persistence\ContentRepository;

use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Exception\NodeTypeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;

final class InMemoryNodeTypeRepository implements NodeTypeRepository
{
    /**
     * @var array<string, NodeType>
     */
    private array $types = [];

    /**
     * @param NodeType[] $seed
     */
    public function __construct(array $seed = [])
    {
        foreach ($seed as $type) {
            $this->types[$type->name] = $type;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(NodeType $type): void
    {
        $this->types[$type->name] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): NodeType
    {
        if (!$this->has($name)) {
            throw new NodeTypeNotFound(\sprintf('Node type "%s" is not registered.', $name));
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return array_values($this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }
}
