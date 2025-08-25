<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

use Aurora\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';
return function (array $context) {
    $env = is_string($context['APP_ENV']) ? $context['APP_ENV'] : 'dev';
    return new Kernel($env, (bool)$context['APP_DEBUG']);
};
