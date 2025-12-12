<?php
session_start();

#Diretório raiz da aplicação web.
define('ROOT', dirname(__file__, 3));

#Extenção padrão da camada de interação com usuário front-end.
define('EXT_VIEW', '.html');

#Diretórios dos arquivos de template da view.
define('DIR_VIEW', ROOT . '/app/view');

#Criamos um constante chamada HOME que guarda automaticamente o endereço principal do site.
define('HOME', $SERVER['HTTP_CF_VISITOR'] . '://' . $_SERVER['HTTP_HOST']);

define('CONFIG_SMIP_EMAIL' , [
    'host' => 'smtp.titan.email',
    'port' => 587,
    'username' => 'noreplay@mkt.fanorte.edu.br',
    'password' => '@906083W@',
    'encryption' => 'Mercantor',
    'from_email' => 'noreplay@mkt.fanorte.edu.br'
]);