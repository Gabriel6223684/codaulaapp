<?php

use Slim\Routing\RouteCollectorProxy;
use app\controller\Cliente;
use app\controller\Fornecedor;
use app\controller\Empresa;
use app\controller\Home;
use app\controller\Login;
use app\controller\User;
use app\Middleware\Middleware;

// Login
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->get('/login', Login::class . ':login');
    $group->post('/precadastro', Login::class . ':precadastro');
    $group->post('/autenticar', Login::class . ':autenticar');
    $group->post('/recuperar-senha', Login::class . ':recuperarSenha');
    $group->post('/validar-codigo', Login::class . ':validarCodigo');
    $group->post('/enviar-codigo-contato', Login::class . ':enviarCodigoContato');
    $group->post('/confirmar-codigo-contato', Login::class . ':confirmarCodigoContato');
    $group->get('/ping', Login::class . ':ping');
});
$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/dashboard', Home::class . ':home');


$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista');
    $group->get('/listuser', User::class . ':listuser');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->get('/alterar/{id}', User::class . ':alterar');
    $group->post('/insert', User::class . ':insert');
    $group->post('/update', User::class . ':update');
});

$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->get('/lista', Cliente::class . ':lista');
    $group->get('/cadastro', Cliente::class . ':cadastro');
    $group->post('/listacliente', Cliente::class . ':listacliente');
    $group->post('/insert', Cliente::class . ':insert');
});

$app->group('/fornecedor', function (RouteCollectorProxy $group) {
    $group->get('/lista', Fornecedor::class . ':lista');
    $group->get('/cadastro', Fornecedor::class . ':cadastro');
    $group->post('/listafornecedor', Fornecedor::class . ':listafornecedor');
    $group->post('/insert', Fornecedor::class . ':insert');
});

$app->group('/empresa', function (RouteCollectorProxy $group) {
    $group->get('/lista', Empresa::class . ':lista');
    $group->get('/cadastro', Empresa::class . ':cadastro');
    $group->post('/listaempresa', Empresa::class . ':listaempresa');
    $group->post('/insert', Empresa::class . ':insert');
});
