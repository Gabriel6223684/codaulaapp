<?php

use Slim\Factory\AppFactory;
use app\database\builder\DeleteQuery;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// ⚙️ Primeiro carregue as configurações e rotas
require __DIR__ . '/../app/helper/settings.php';
require __DIR__ . '/../app/route/route.php';

// 🧹 Depois execute seu delete (com segurança)
try {
    DeleteQuery::table('cliente')->where('id', '=', '1')->delete();
} catch (Exception $e) {
    error_log('Erro ao excluir cliente: ' . $e->getMessage());
}

$app->run();
