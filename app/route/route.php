<?php

use app\controller\Home;
use app\controller\Login;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/login', Login::class . ':login');
$app->post('/login/precadastro', [\app\controller\Login::class, 'precadastro']);


$app->group('/usuario', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
});
$app->group('/login', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
});
