<?php

use Labstag\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return fn(array $context): \Labstag\Kernel => new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
