<?php

use Slim\Routing\RouteCollectorProxy;
use app\middleware\AuthMiddleware;
use app\controller\Home;
use app\controller\Login;
use app\controller\User;
<<<<<<< HEAD

$authMiddleware = new AuthMiddleware();

// Rotas públicas
=======
use Slim\Routing\RouteCollectorProxy;

// Home
$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');

// Login
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->get('', Login::class . ':login');
    $group->post('', Login::class . ':autenticar');
    $group->post('/precadastro', Login::class . ':precadastro');
<<<<<<< HEAD
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
=======
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
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
