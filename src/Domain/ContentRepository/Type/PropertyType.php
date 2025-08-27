<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Type;

/**
 * Enum PropertyType.
 *
 * Represents the supported property types in the content repository.
 */
enum PropertyType: string
{
    /** String value */
    case STRING = 'string';

    /** Integer value */
    case INT = 'int';

    /** Floating point value */
    case FLOAT = 'float';

    /** Boolean value */
    case BOOL = 'bool';

    /** DateTime value */
    case DATETIME = 'datetime';

    /** JSON value */
    case JSON = 'json';

    /** Array value */
    case ARRAY = 'array';

    /** Reference to another node (NodeId as string) */
    case REFERENCE = 'reference';
}
