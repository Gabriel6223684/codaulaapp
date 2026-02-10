<?php

use app\controller\Home;
use app\controller\Login;
use app\controller\PaymentTerms;
use app\controller\Sale;
use app\controller\User;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/login', Login::class . ':login');

$app->group('/home', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
});
$app->group('/venda', function (RouteCollectorProxy $group) {
    $group->get('/lista', Sale::class . ':lista');
    $group->get('/cadastro', Sale::class . ':cadastro');
});
$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->get('/alterar/{id}', User::class . ':alterar');
    $group->get('/print', User::class . ':print');
    $group->post('/insert', User::class . ':insert');
    $group->post('/update', User::class . ':update');
});
$app->group('/pagamento', function (RouteCollectorProxy $group) {
    $group->get('/lista', PaymentTerms::class . ':lista');
    $group->get('/cadastro', PaymentTerms::class . ':cadastro');
    $group->get('/alterar/{id}', PaymentTerms::class . ':alterar');
    $group->post('/insert', PaymentTerms::class . ':insert');
    $group->post('/update', PaymentTerms::class . ':update');
    $group->post('/insertinstallment', PaymentTerms::class . ':insertInstallment');
    $group->post('/loaddatainstallments', PaymentTerms::class . ':loaddatainstallments');
    $group->post('/deleteinstallment', PaymentTerms::class . ':deleteinstallment');
});