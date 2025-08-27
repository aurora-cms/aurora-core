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

/**
 * Represents a node type in the content repository.
 *
 * @readonly
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
     * @param string                    $name        the name of the node type
     * @param array<PropertyDefinition> $definitions list of property definitions
     *
     * @throws \InvalidArgumentException if the node type name format is invalid
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

    /**
     * Checks if a property definition exists for the given name.
     *
     * @param string $name property name
     *
     * @return bool true if the property exists, false otherwise
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->definitions);
    }

    /**
     * Returns the property definition for the given name.
     *
     * @param string $name property name
     *
     * @return PropertyDefinition the property definition
     *
     * @throws UndefinedProperty if the property is not defined
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
     * @param array<string, mixed> $properties properties to validate
     *
     * @throws \Exception if validation fails for any property
     */
    public function validateProperties(array $properties): void
    {
        foreach ($properties as $name => $value) {
            $this->definition($name)->validate($value);
        }
    }

    /**
     * Returns the node type name as a string.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
