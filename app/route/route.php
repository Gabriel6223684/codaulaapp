<?php

use app\controller\Home;
use app\controller\Login;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/login', Login::class . ':login');

$app->group('/home', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
});