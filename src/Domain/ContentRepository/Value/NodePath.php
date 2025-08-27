<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

use Aurora\Domain\ContentRepository\Exception\NodePathInvalid;

/**
 * Regular expression for valid node path segments.
 * Must start with a lowercase letter or digit, followed by lowercase letters, digits, underscores, or hyphens.
 */
const PATH_REGEX = '/^[a-z0-9][a-za0-9_\-]*$/';

/**
 * Represents a canonical node path in the content repository.
 * Paths are always absolute, start with '/', and do not have trailing slashes except for the root node.
 */
final class NodePath implements \Stringable
{
    /**
     * @var string Canonical path, e.g. /parent/child/node (no trailing slash, except for root node "/")
     */
    private string $path;

    /**
     * Constructs a NodePath instance.
     *
     * @param string $path canonical path string
     */
    private function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Returns the root node path ("/").
     */
    public static function root(): self
    {
        return new self('/');
    }

    /**
     * Creates a NodePath from a string, validating its format.
     *
     * @throws NodePathInvalid if the path is invalid
     */
    public static function fromString(string $path): self
    {
        $p = trim($path);
        if ('' === $p || '/' !== $p[0]) {
            throw new NodePathInvalid();
        }

        $segments = array_values(array_filter(explode('/', $p), fn ($s) => '' !== $s));
        foreach ($segments as $segment) {
            if ('.' === $segment || '..' === $segment) {
                throw new NodePathInvalid();
            }
            if (!preg_match(PATH_REGEX, $segment)) {
                throw new NodePathInvalid();
            }
        }

        $canonical = '/'.implode('/', $segments);

        return '/' === $canonical ? new self('/') : new self(rtrim($canonical, '/'));
    }

    /**
     * Appends a segment to the current node path.
     *
     * @throws NodePathInvalid if the segment is invalid
     */
    public function append(string $segment): self
    {
        $seg = strtolower(trim($segment));
        if (!preg_match(PATH_REGEX, $seg)) {
            throw new NodePathInvalid();
        }

        return $this->isRoot() ? new self('/'.$seg) : new self($this->path.'/'.$seg);
    }

    /**
     * Returns the parent node path.
     * If called on the root node, returns itself.
     */
    public function parent(): self
    {
        if ($this->isRoot()) {
            return $this;
        }

        $parts = explode('/', $this->path);
        array_pop($parts);
        $parent = implode('/', $parts);

        return '' === $parent ? self::root() : new self($parent);
    }

    /**
     * Returns the name of the node (last segment of the path).
     * Returns an empty string for the root node.
     */
    public function name(): string
    {
        return $this->isRoot() ? '' : basename($this->path);
    }

    /**
     * Checks if the node path is the root node.
     */
    public function isRoot(): bool
    {
        return '/' === $this->path;
    }

    /**
     * Checks if the current path starts with another node path.
     */
    public function startsWith(self $other): bool
    {
        return $other->isRoot() || str_starts_with($this->path.'/', $other->path.'/');
    }

    /**
     * Checks if the current path is equal to another node path.
     */
    public function equals(self $other): bool
    {
        return $this->path === $other->path;
    }

    /**
     * Returns the canonical path as a string.
     */
    public function __toString(): string
    {
        return $this->path;
    }
}
