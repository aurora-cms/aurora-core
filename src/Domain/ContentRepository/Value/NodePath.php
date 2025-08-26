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

const PATH_REGEX = '/^[a-z0-9][a-za0-9_\-]*$/';

final class NodePath implements \Stringable
{
    /**
     * @var string canonical path, e.g. /parent/child/node  (no trailing slash, except for root node "/")
     */
    private string $path;

    private function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function root(): self
    {
        return new self('/');
    }

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

    public function append(string $segment): self
    {
        $seg = strtolower(trim($segment));
        if (!preg_match(PATH_REGEX, $seg)) {
            throw new NodePathInvalid();
        }

        return $this->isRoot() ? new self('/'.$seg) : new self($this->path.'/'.$seg);
    }

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

    public function name(): string
    {
        return $this->isRoot() ? '' : basename($this->path);
    }

    public function isRoot(): bool
    {
        return '/' === $this->path;
    }

    public function startsWith(self $other): bool
    {
        return $other->isRoot() || str_starts_with($this->path.'/', $other->path.'/');
    }

    public function equals(self $other): bool
    {
        return $this->path === $other->path;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
