<?php

use app\database\builder\DeleteQuery;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

DeleteQuery::table('cliente')->where('id','=','1')->delete();

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

require __DIR__ . '/../app/helper/settings.php';
require __DIR__ . '/../app/route/route.php';

$app->run();