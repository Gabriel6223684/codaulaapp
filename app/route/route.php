<?php

use Slim\Routing\RouteCollectorProxy;
use app\middleware\AuthMiddleware;
use app\controller\Home;
use app\controller\Login;
use app\controller\User;

$authMiddleware = new AuthMiddleware();

// Rotas públicas
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->get('', Login::class . ':login');
    $group->post('', Login::class . ':autenticar');
    $group->post('/precadastro', Login::class . ':precadastro');
    $group->post('/recuperar-senha', Login::class . ':recuperarSenha');
});

$app->get('/ping', Login::class . ':ping');

// Rotas protegidas
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/', Home::class . ':home');
    $group->get('/home', Home::class . ':home');

    $group->group('/usuario', function ($group) {
        $group->get('/listuser', User::class . ':listuser');
        $group->get('/cadastro', User::class . ':cadastro');
        $group->get('/alterar/{id}', User::class . ':alterar');
        $group->post('/insert', User::class . ':insert');
        $group->post('/update', User::class . ':update');
    });
})->add($authMiddleware); // <-- middleware aplicado ao grupo protegido
