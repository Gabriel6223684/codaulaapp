<?php

use app\controller\Home;
use app\controller\Login;
use Slim\Routing\RouteCollectorProxy;


$app->post('/', Home::class . ':home');
$app->post('/home', Home::class . ':home');


$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('/login', Login::class . ':login');
});