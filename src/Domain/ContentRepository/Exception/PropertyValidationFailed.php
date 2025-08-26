<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Domain\ContentRepository\Exception;

class PropertyValidationFailed extends \DomainException
{
    public function __construct(string $message = 'Property validation failed')
    {
        parent::__construct($message);
    }
}
