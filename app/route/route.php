<?php

use app\controller\Home;
use app\controller\Login;
use app\controller\PaymentTerms;
use app\controller\Sale;
use app\controller\User;
use app\controller\Product;
use app\controller\Supplier;
use app\controller\Client;
use app\controller\Company;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/login', Login::class . ':login');
$app->get('/sale', Sale::class . ':cadastro');

$app->group('/home', function (RouteCollectorProxy $group) {
    #$group->post('/tema', Home::class . ':tema');
});
$app->group('/produto', function (RouteCollectorProxy $group) {
    $group->post('/listproductdata', Product::class . ':listproductdata');
});
$app->group('/product', function (RouteCollectorProxy $group) {
    $group->get('/lista', Product::class . ':lista');
    $group->get('/cadastro', Product::class . ':cadastro');
    $group->get('/alterar/{id}', Product::class . ':alterar');
    $group->get('/print', Product::class . ':print');
    $group->post('/insert', Product::class . ':insert');
    $group->post('/update', Product::class . ':update');
    $group->delete('/deletar/{id}', Product::class . ':deletar');
});
$app->group('/venda', function (RouteCollectorProxy $group) {
    $group->get('/lista', Sale::class . ':lista');
    $group->get('/cadastro', Sale::class . ':cadastro');
    $group->get('/alterar/{id}', Sale::class . ':alterar');
    $group->get('/get/{id}', Sale::class . ':get');
    $group->get('/getinstallments/{id}', Sale::class . ':getInstallments');
    $group->get('/print', Sale::class . ':print');
    $group->post('/insert', Sale::class . ':insert');
    $group->post('/update', Sale::class . ':update');
    $group->post('/insertitem', Sale::class . ':insertItem');
    $group->post('/savepaymentterms', Sale::class . ':savePaymentTerms');
    $group->post('/createinstallments', Sale::class . ':createInstallments');
    $group->get('/getpaymentterms', Sale::class . ':getPaymentTerms');
    $group->delete('/deletar/{id}', Sale::class . ':deletar');
    $group->delete('/deleteitem/{id}', Sale::class . ':deleteItem');
});
$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->get('/alterar/{id}', User::class . ':alterar');
    $group->get('/print', User::class . ':print');
    $group->post('/insert', User::class . ':insert');
    $group->post('/update', User::class . ':update');
    $group->delete('/deletar/{id}', User::class . ':deletar');
});
$app->group('/pagamento', function (RouteCollectorProxy $group) {
    $group->get('/lista', PaymentTerms::class . ':lista');
    $group->get('/cadastro', PaymentTerms::class . ':cadastro');
    $group->get('/alterar/{id}', PaymentTerms::class . ':alterar');
    $group->get('/print', PaymentTerms::class . ':print');
    $group->post('/insert', PaymentTerms::class . ':insert');
    $group->post('/update', PaymentTerms::class . ':update');
    $group->post('/insertinstallment', PaymentTerms::class . ':insertInstallment');
    $group->post('/loaddatainstallments', PaymentTerms::class . ':loaddatainstallments');
    $group->post('/deleteinstallment', PaymentTerms::class . ':deleteinstallment');
    $group->post('/listapaymentterms', PaymentTerms::class . ':listapaymentterms');
    $group->delete('/deletar/{id}', PaymentTerms::class . ':deletar');
});
$app->group('/supplier', function (RouteCollectorProxy $group) {
    $group->get('/lista', Supplier::class . ':lista');
    $group->get('/cadastro', Supplier::class . ':cadastro');
    $group->get('/alterar/{id}', Supplier::class . ':alterar');
    $group->get('/print', Supplier::class . ':print');
    $group->post('/insert', Supplier::class . ':insert');
    $group->post('/update', Supplier::class . ':update');
    $group->delete('/deletar/{id}', Supplier::class . ':deletar');
});
$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->get('/lista', Client::class . ':lista');
    $group->get('/cadastro', Client::class . ':cadastro');
    $group->get('/alterar/{id}', Client::class . ':alterar');
    $group->get('/print', Client::class . ':print');
    $group->post('/insert', Client::class . ':insert');
    $group->post('/update', Client::class . ':update');
    $group->delete('/deletar/{id}', Client::class . ':deletar');
});
$app->group('/empresa', function (RouteCollectorProxy $group) {
    $group->get('/lista', Company::class . ':lista');
    $group->get('/cadastro', Company::class . ':cadastro');
    $group->get('/alterar/{id}', Company::class . ':alterar');
    $group->get('/print', Company::class . ':print');
    $group->post('/insert', Company::class . ':insert');
    $group->post('/update', Company::class . ':update');
    $group->delete('/deletar/{id}', Company::class . ':deletar');
});
