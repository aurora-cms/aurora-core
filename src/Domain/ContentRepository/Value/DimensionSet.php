<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Value;

final class DimensionSet
{
    /**
     * @var array<string, string>
     */
    private array $values;

    /**
     * @param array<string, string> $values
     */
    public function __construct(array $values = [])
    {
        if (!self::isValid($values)) {
            throw new \InvalidArgumentException('Invalid dimension set values.');
        }
        ksort($values);
        $this->values = $values;
    }

    public static function isValid(array $values): bool
    {
        return array_all($values, fn ($v, $k) => \is_string($k) && '' !== $k && \is_string($v) && '' !== $v);
    }

    /**
     * @return array<string, string>
     */
    public function values(): array
    {
        return $this->values;
    }

    public function equals(self $other): bool
    {
        return $this->values === $other->values;
    }

    public function toKey(): string
    {
        if (empty($this->values)) {
            return '∅';
        }

        $parts = [];
        foreach ($this->values as $k => $v) {
            $parts[] = rawurlencode($k).'='.rawurlencode(\is_scalar($v) || null === $v ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return implode(';', $parts);
    }

    public function __toString(): string
    {
        return $this->toKey();
    }
}
