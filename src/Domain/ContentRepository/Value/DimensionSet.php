<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

/**
 * A single dimension set, e.g. ['locale' => 'en_US', 'device' => 'mobile', 'channel' => 'web'].
 *
 * This class represents an immutable set of dimensions as key-value pairs.
 * Dimension keys are normalized to lowercase and trimmed of whitespace.
 * Invalid or empty dimension names/values are not allowed.
 */
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
     * DimensionSet constructor.
     *
     * @param array<string, string> $values associative array of dimension name => dimension value
     *
     * @throws \InvalidArgumentException if a dimension name or value is empty or invalid
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

    /**
     * Returns an empty DimensionSet.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Returns all dimensions as an associative array.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Checks if this DimensionSet is equal to another.
     */
    public function equals(self $other): bool
    {
        return $this->values === $other->values;
    }

    /**
     * Returns a string representation of the DimensionSet.
     */
    public function __toString(): string
    {
        return empty($this->values) ? '{}' : '{'.implode(';', array_map(fn (string $k, string $v) => "$k=$v", array_keys($this->values), $this->values)).'}';
    }
}
