<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\ContentRepository\NodeTypes;

use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Infrastructure\ContentRepository\NodeTypes\Exception\InvalidNodeTypeDefinition;

/**
 * Resolves raw definitions into NodeType domain objects with inheritance & merging.
 */
final class NodeTypeResolver
{
    /**
     * @param array<string, array{extends?: string|null, properties?: array<string, array{type: string, nullable?: bool, multiple?: bool}>}> $definitions
     *
     * @return array<string, NodeType>
     */
    public function resolve(array $definitions): array
    {
        $resolving = [];
        $resolved = [];

        $build = function (string $name) use (&$build, &$resolving, &$resolved, $definitions): NodeType {
            if (isset($resolved[$name])) {
                return $resolved[$name];
            }
            if (!isset($definitions[$name])) {
                throw new InvalidNodeTypeDefinition(\sprintf('Unknown parent node type "%s".', $name));
            }
            if (isset($resolving[$name])) {
                throw new InvalidNodeTypeDefinition(\sprintf('Circular inheritance detected for node type "%s".', $name));
            }

            $resolving[$name] = true;
            $def = $definitions[$name];

            $inherited = [];
            if (!empty($def['extends'])) {
                $parent = $build((string) $def['extends']);
                // Pull parent definitions
                $parentProps = $this->extractPropertyDefinitions($parent);
                foreach ($parentProps as $pd) {
                    $inherited[$pd->name] = $pd;
                }
            }

            // Merge/override with own properties
            $own = [];
            $props = $def['properties'] ?? [];
            foreach ($props as $propName => $p) {
                $own[$propName] = $this->createPropertyDefinition($propName, $p['type'], $p['nullable'] ?? false, $p['multiple'] ?? false);
            }

            $final = array_values(array_merge($inherited, $own));

            $resolved[$name] = new NodeType($name, $final);
            unset($resolving[$name]);

            return $resolved[$name];
        };

        foreach (array_keys($definitions) as $name) {
            $build($name);
        }

        return $resolved;
    }

    /**
     * @return list<PropertyDefinition>
     */
    private function extractPropertyDefinitions(NodeType $type): array
    {
        // NodeType doesn't expose all definitions; reflect to fetch private field safely.
        // This is internal to infrastructure for merging only.
        $rp = new \ReflectionProperty(NodeType::class, 'definitions');
        $rp->setAccessible(true);
        /** @var array<string, PropertyDefinition> $defs */
        $defs = $rp->getValue($type);

        return array_values($defs);
    }

    private function createPropertyDefinition(string $name, string $type, bool $nullable, bool $multiple): PropertyDefinition
    {
        $enum = PropertyType::tryFrom(strtolower($type));
        if (!$enum) {
            throw new InvalidNodeTypeDefinition(\sprintf('Unknown property type "%s" for "%s".', $type, $name));
        }

        return new PropertyDefinition($name, $enum, $nullable, $multiple);
    }
}
