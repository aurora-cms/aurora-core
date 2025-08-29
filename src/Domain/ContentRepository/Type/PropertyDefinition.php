<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Type;

use Aurora\Domain\ContentRepository\Exception\PropertyValidationFailed;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_scalar;
use function is_string;
use function sprintf;

/**
 * Defines a property for content repository types.
 *
 * @readonly
 */
final readonly class PropertyDefinition
{
    /**
     * @param string       $name     Property name (alphanumeric and underscores only)
     * @param PropertyType $type     Property type
     * @param bool         $nullable Whether the property can be null
     * @param bool         $multiple Whether the property can hold multiple values (array)
     *
     * @throws InvalidArgumentException If the property name format is invalid
     */
    public function __construct(
        public string $name,
        public PropertyType $type,
        public bool $nullable = false,
        public bool $multiple = false,
    ) {
        if (!preg_match('/^[a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('Property name format invalid: '.$name);
        }
    }

    /**
     * Validates a value against the property definition.
     *
     * @param mixed $value The value to validate
     *
     * @throws InvalidArgumentException If the value is null and not allowed
     * @throws PropertyValidationFailed  If the value does not match the property type
     */
    public function validate(mixed $value): void
    {
        if (null === $value) {
            if (!$this->nullable) {
                throw new InvalidArgumentException(sprintf('Property "%s" is not nullable.', $this->name));
            }

            return;
        }

        $check = function (mixed $v): void {
            switch ($this->type) {
                case PropertyType::STRING: if (!is_string($v)) {
                    throw new PropertyValidationFailed('Expected string');
                } break;
                case PropertyType::INT: if (!is_int($v)) {
                    throw new PropertyValidationFailed('Expected int');
                } break;
                case PropertyType::FLOAT: if (!is_float($v) && !is_int($v)) {
                    throw new PropertyValidationFailed('Expected float');
                } break;
                case PropertyType::BOOL: if (!is_bool($v)) {
                    throw new PropertyValidationFailed('Expected bool');
                } break;
                case PropertyType::DATETIME: if (!$v instanceof DateTimeInterface) {
                    throw new PropertyValidationFailed('Expected DateTimeInterface');
                } break;
                case PropertyType::JSON: if (!is_scalar($v) && !is_array($v) && !($v instanceof JsonSerializable)) {
                    throw new PropertyValidationFailed('Expected json-serializable');
                } break;
                case PropertyType::ARRAY: if (!is_array($v)) {
                    throw new PropertyValidationFailed('Expected array');
                } break;
                case PropertyType::REFERENCE: if (!is_string($v) || !preg_match('/^[A-Za-z0-9\-]{6,}$/', $v)) {
                    throw new PropertyValidationFailed('Expected reference id string');
                } break;
            }
        };

        if ($this->multiple) {
            if (!is_array($value)) {
                throw new PropertyValidationFailed(sprintf('Property "%s" is multiple, expected array.', $this->name));
            }
            foreach ($value as $v) {
                $check($v);
            }
        } else {
            $check($value);
        }
    }
}
