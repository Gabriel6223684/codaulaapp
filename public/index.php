<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Middleware para parsear JSON automaticamente
$app->addBodyParsingMiddleware();

require __DIR__ . '/../app/helper/settings.php';
require __DIR__ . '/../app/route/route.php';

$app->run();
