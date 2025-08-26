<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

use Aurora\Shared\Kernel\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$env = is_string($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'dev';
$kernel = new Kernel($env, (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// @phpstan-ignore-next-line
return $kernel->getContainer()->get('doctrine')->getManager();
