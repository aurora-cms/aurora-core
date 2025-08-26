<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Type;

use Aurora\Domain\ContentRepository\Exception\UndefinedProperty;

final readonly class NodeType implements \Stringable
{
    /** @var array<string, PropertyDefinition> */
    private array $definitions;

    /**
     * @param array<PropertyDefinition> $definitions
     */
    public function __construct(public string $name, array $definitions = [])
    {
        if (!preg_match('/^[a-zA-z][a-zA-Z0-9_.-]*$/', $name)) {
            throw new \InvalidArgumentException('Node type name format invalid: '.$name);
        }

        $defs = [];
        foreach ($definitions as $definition) {
            $defs[$definition->name] = $definition;
        }

        $this->definitions = $defs;
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->definitions);
    }

    public function definition(string $name): PropertyDefinition
    {
        if (!$this->has($name)) {
            throw new UndefinedProperty("Undefined property '$name' in node type '{$this->name}'");
        }

        return $this->definitions[$name];
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function validateProperties(array $properties): void
    {
        foreach ($properties as $name => $value) {
            $this->definition($name)->validate($value);
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
