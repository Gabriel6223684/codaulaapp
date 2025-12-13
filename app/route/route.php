<?php

use app\controller\Home;
use app\controller\Login;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/login', Login::class . ':login');
$app->post('/login/precadastro', [\app\controller\Login::class, 'precadastro']);

$app->get('/listuser', [\app\controller\User::class, 'listuser']);

$app->group('/usuario', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
    $group->get('/listuser', [\app\controller\User::class, 'listuser']);
    $group->get('/cadastro', User::class . ':cadastro');
    $group->get('/alterar/{id}', User::class . ':alterar');
    $group->post('/insert', User::class . ':insert');
    $group->post('/update', User::class . ':update');
});

$app->group('/login', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
});

