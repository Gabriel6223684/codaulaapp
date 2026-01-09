<?php
session_start();

#Diretório raiz da aplicação web.
define('ROOT', dirname(__file__, 3));

#Extenção padrão da camada de interação com usuário front-end.
define('EXT_VIEW', '.html');

#Diretórios dos arquivos de template da view.
define('DIR_VIEW', ROOT . '/app/view');

#Criamos um constante chamada HOME que guarda automaticamente o endereço principal do site.
define('HOME', (isset($_SERVER['HTTP_CF_VISITOR']) ? $_SERVER['HTTP_CF_VISITOR'] : 'http') . '://' . $_SERVER['HTTP_HOST']);

// Configuração de SMTP utilizada pela classe Email
define('CONFIG_SMTP_EMAIL', [
    'host' => 'smtp.titan.email',
    'port' => 587,
    'user' => 'noreplay@mkt.fanorte.edu.br',
    'passwd' => '@906083W@',
    'encryption' => 'tls', // 'tls' ou 'ssl'
    'from_email' => 'noreplay@mkt.fanorte.edu.br',
    'from_name' => 'No Reply'
]);
