<?php

use app\controller\Cliente;
use app\controller\User;
use app\controller\Home;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');

$app->get('/home', Home::class . ':home');

$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->post('/insert', User::class . ':insert');
    $group->post('/listuser', User::class . ':listuser');
});

$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->get('/lista', Cliente::class . ':lista');
    $group->get('/cadastro', Cliente::class . ':cadastro');
});
