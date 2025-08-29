<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\ContentRepository\NodeTypes;

use Aurora\Infrastructure\ContentRepository\NodeTypes\Exception\InvalidNodeTypeDefinition;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads raw NodeType definitions from YAML/JSON files.
 *
 * Expected file structure (YAML example):
 *
 *   article:
 *     extends: 'document'
 *     properties:
 *       title: { type: string, nullable: false }
 *       tags:  { type: string, multiple: true }
 */
final class DefinitionLoader
{
    /**
     * @param list<string>|null $paths Optional explicit search paths. If null/empty, defaults to '<project>/config/node_types'.
     */
    public function __construct(private readonly ?array $paths = null)
    {
    }

    /**
     * @return array<string, array{extends: string|null, properties?: array<string, array{type: string, nullable?: bool, multiple?: bool}>}>
     */
    public function load(): array
    {
        $definitions = [];

        $paths = $this->paths ?? [$this->defaultPath()];
        foreach ($paths as $dir) {
            if (!is_dir($dir)) {
                // silently ignore non-existing directories for flexibility
                continue;
            }

            $files = scandir($dir) ?: [];
            foreach ($files as $file) {
                if ('.' === $file || '..' === $file) {
                    continue;
                }
                $path = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
                if (!is_file($path)) {
                    continue;
                }

                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (!\in_array($ext, ['yml', 'yaml', 'json'], true)) {
                    continue;
                }

                $data = $this->parseFile($path, $ext);
                if (!\is_array($data)) {
                    throw new InvalidNodeTypeDefinition(\sprintf('File %s must define an object at the root.', $path));
                }

                foreach ($data as $name => $def) {
                    if (!\is_string($name)) {
                        throw new InvalidNodeTypeDefinition(\sprintf('Invalid node type key in %s. Expected string, got %s.', $path, get_debug_type($name)));
                    }
                    if (!\is_array($def)) {
                        throw new InvalidNodeTypeDefinition(\sprintf('Definition for "%s" in %s must be a map/object.', $name, $path));
                    }

                    if (isset($definitions[$name])) {
                        throw new InvalidNodeTypeDefinition(\sprintf('Duplicate node type "%s" found in %s.', $name, $path));
                    }

                    // Normalize structure
                    /** @var string|null $extends */
                    $extends = (isset($def['extends']) && \is_string($def['extends'])) ? $def['extends'] : null;
                    $properties = [];
                    if (isset($def['properties'])) {
                        if (!\is_array($def['properties'])) {
                            throw new InvalidNodeTypeDefinition(\sprintf('"properties" of "%s" must be a map/object.', $name));
                        }
                        foreach ($def['properties'] as $propName => $prop) {
                            if (!\is_string($propName)) {
                                throw new InvalidNodeTypeDefinition(\sprintf('Invalid property name for "%s"; expected string.', $name));
                            }
                            if (!\is_array($prop)) {
                                throw new InvalidNodeTypeDefinition(\sprintf('Property "%s.%s" must be a map/object.', $name, $propName));
                            }
                            if (!isset($prop['type'])) {
                                throw new InvalidNodeTypeDefinition(\sprintf('Property "%s.%s" must declare a "type".', $name, $propName));
                            }
                            if (!\is_string($prop['type'])) {
                                throw new InvalidNodeTypeDefinition(\sprintf('Property "%s.%s" type must be a string.', $name, $propName));
                            }
                            $properties[$propName] = [
                                'type' => $prop['type'],
                                'nullable' => \array_key_exists('nullable', $prop) ? (bool) $prop['nullable'] : false,
                                'multiple' => \array_key_exists('multiple', $prop) ? (bool) $prop['multiple'] : false,
                            ];
                        }
                    }

                    $definitions[$name] = [
                        'extends' => $extends,
                        'properties' => $properties,
                    ];
                }
            }
        }

        return $definitions;
    }

    private function parseFile(string $path, string $ext): mixed
    {
        try {
            if ('json' === $ext) {
                $json = file_get_contents($path);
                if (false === $json) {
                    throw new \RuntimeException('Failed to read file.');
                }
                $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

                return $data;
            }

            // yaml / yml
            return Yaml::parseFile($path);
        } catch (\Throwable $e) {
            throw new InvalidNodeTypeDefinition(\sprintf('Failed parsing %s: %s', $path, $e->getMessage()), previous: $e);
        }
    }

    private function defaultPath(): string
    {
        // src/Infrastructure/ContentRepository/NodeTypes -> up 4 = project root
        $projectDir = \dirname(__DIR__, 4);

        return $projectDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'node_types';
    }
}
