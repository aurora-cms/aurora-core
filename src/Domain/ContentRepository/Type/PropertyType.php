<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Type;

enum PropertyType: string
{
    case STRING = 'string';
    case INT = 'int';
    case FLOAT = 'float';
    case BOOL = 'bool';
    case DATETIME = 'datetime';
    case JSON = 'json';
    case ARRAY = 'array';
    case REFERENCE = 'reference';
}
