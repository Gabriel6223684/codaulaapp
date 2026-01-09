<?php

use app\controller\Home;
use app\controller\Login;
use app\controller\User;
use Slim\Routing\RouteCollectorProxy;

// Home
$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');

// Login
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->get('', Login::class . ':login');
    $group->post('', Login::class . ':autenticar');
    $group->post('/precadastro', Login::class . ':precadastro');
    $group->post('/enviar-codigo-contato', Login::class . ':enviarCodigoContato');
    $group->post('/confirmar-codigo-contato', Login::class . ':confirmarCodigoContato');
    $group->post('/recuperar-senha', Login::class . ':recuperarSenha');
    $group->post('/validar-codigo', Login::class . ':validarCodigo');
});

// Health check / debug endpoint
$app->get('/ping', Login::class . ':ping');

// Usuário
$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/listuser', User::class . ':listuser');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->get('/alterar/{id}', User::class . ':alterar');
    $group->post('/insert', User::class . ':insert');
    $group->post('/update', User::class . ':update');
});
