<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../src/dependencies.php';

$app = AppFactory::create();

(require __DIR__ . '/../src/Routes/api.php')($app, $container);

$app->run();
