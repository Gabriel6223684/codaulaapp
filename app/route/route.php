<?php

use Slim\Routing\RouteCollectorProxy;
use app\controller\Home;
use app\controller\Login;
use app\controller\User;
use app\middleware\Middleware;

// =====================
// ROTAS PÚBLICAS
// =====================

// Login
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->get('', Login::class . ':login');
    $group->post('', Login::class . ':autenticar');

    $group->post('/precadastro', Login::class . ':precadastro');
    $group->post('/recuperar-senha', Login::class . ':recuperarSenha');
    $group->post('/validar-codigo', Login::class . ':validarCodigo');
    $group->post('/enviar-codigo-contato', Login::class . ':enviarCodigoContato');
    $group->post('/confirmar-codigo-contato', Login::class . ':confirmarCodigoContato');
});

// Health check
$app->get('/ping', Login::class . ':ping');


// =====================
// ROTAS PROTEGIDAS
// =====================

$app->group('', function (RouteCollectorProxy $group) {

    // Home
    $group->get('/', Home::class . ':home');
    $group->get('/home', Home::class . ':home');

    // Usuário
    $group->group('/usuario', function (RouteCollectorProxy $group) {
        $group->get('/listuser', User::class . ':listuser');
        $group->get('/cadastro', User::class . ':cadastro');
        $group->get('/alterar/{id}', User::class . ':alterar');
        $group->post('/insert', User::class . ':insert');
        $group->post('/update', User::class . ':update');
    });

})->add(\app\middleware\Middleware::authentication());
