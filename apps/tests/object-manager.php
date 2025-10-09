<?php

namespace Labstag\Tests;

// tests/object-manager.php
use Labstag\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__ . '/../.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$doctrine  = $container->get('doctrine');

return $doctrine->getManager();
