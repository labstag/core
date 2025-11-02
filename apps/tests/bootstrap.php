<?php

namespace Labstag\Tests;

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Force test env before loading .env chain so that .env.test is used
if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    $_SERVER['APP_ENV'] = 'test';
    $_ENV['APP_ENV'] = 'test';
}

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    include dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    $dotenv = new Dotenv();
    $dotenv->bootEnv(dirname(__DIR__) . '/.env');
}

if (!empty($_SERVER['APP_DEBUG'])) {
    umask(0000);
}
