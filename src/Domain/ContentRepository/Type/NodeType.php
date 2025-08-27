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
use InvalidArgumentException;

/**
 * Represents a node type in the content repository.
 *
 * @readonly
 * @implements \Stringable
 */
final readonly class NodeType implements \Stringable
{
    /**
     * Property definitions for this node type.
     *
     * @var array<string, PropertyDefinition>
     */
    private array $definitions;

    /**
     * Constructs a new NodeType.
     *
     * @param string $name The name of the node type.
     * @param array<PropertyDefinition> $definitions List of property definitions.
     * @throws InvalidArgumentException If the node type name format is invalid.
     */
    public function __construct(public string $name, array $definitions = [])
    {
        if (!preg_match('/^[a-zA-z][a-zA-Z0-9_.-]*$/', $name)) {
            throw new InvalidArgumentException('Node type name format invalid: '.$name);
        }

        $defs = [];
        foreach ($definitions as $definition) {
            $defs[$definition->name] = $definition;
        }

        $this->definitions = $defs;
    }

    /**
     * Checks if a property definition exists for the given name.
     *
     * @param string $name Property name.
     * @return bool True if the property exists, false otherwise.
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->definitions);
    }

    /**
     * Returns the property definition for the given name.
     *
     * @param string $name Property name.
     * @return PropertyDefinition The property definition.
     * @throws UndefinedProperty If the property is not defined.
     */
    public function definition(string $name): PropertyDefinition
    {
        if (!$this->has($name)) {
            throw new UndefinedProperty("Undefined property '$name' in node type '{$this->name}'");
        }

        return $this->definitions[$name];
    }

    /**
     * Validates the given properties against the property definitions.
     *
     * @param array<string, mixed> $properties Properties to validate.
     * @throws \Exception If validation fails for any property.
     */
    public function validateProperties(array $properties): void
    {
        foreach ($properties as $name => $value) {
            $this->definition($name)->validate($value);
        }
    }

    /**
     * Returns the node type name as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
