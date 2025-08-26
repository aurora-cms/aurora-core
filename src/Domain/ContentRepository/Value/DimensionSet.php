<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

/**
 * Represents a set of dimensions in a key-value structure where the dimension keys are normalized
 * to lowercase and are stripped of whitespace. Implements immutability.
 */

namespace Aurora\Domain\ContentRepository\Value;

final readonly class DimensionSet implements \Stringable
{
    /**
     * Associative array of dimension name => dimension value.
     * The dimension keys are normalized to lowercase and trimmed of whitespace.
     *
     * @var array<string, string>
     */
    private array $values;

    /**
     * @param array<string, string> $values Associative array of dimension name => dimension value
     */
    public function __construct(array $values = [])
    {
        $norm = [];
        foreach ($values as $k => $v) {
            $key = strtolower(trim($k));
            if ('' === $key || '' === $v) {
                throw new \InvalidArgumentException('Dimension name and value cannot be empty.');
            }
            if (!preg_match('/^[a-z][a-z0-9_\-]*$/', $key)) {
                throw new \InvalidArgumentException(\sprintf('Invalid dimension name: "%s". Must start with a letter and contain only letters, numbers, underscores, or hyphens.', $k));
            }
            $norm[$key] = $v;
        }
        ksort($norm);
        $this->values = $norm;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }

    public function equals(self $other): bool
    {
        return $this->values === $other->values;
    }

    public function __toString(): string
    {
        return empty($this->values) ? '{}' : '{'.implode(',', $this->values).'}';
    }
}
