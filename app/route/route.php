<?php

use app\controller\User;
use app\controller\Home;
use app\controller\Cliente;
use app\controller\Fornecedor;
use app\controller\Empresa;
use app\controller\Login;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');

$app->get('/home', Home::class . ':home');

$app->get('/login', Login::class . ':login');

$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->post('/listuser', User::class . ':listuser');
    $group->post('/insert', User::class . ':insert');
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

$app->group('/login', function (RouteCollectorProxy $group) {
     $group->post('/precadastro', Login::class . ':precadastro');
});
