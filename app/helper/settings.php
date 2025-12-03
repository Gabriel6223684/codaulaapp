<?php
session_start();

#Diretório raiz da aplicação web.
define('ROOT', dirname(__file__, 3));

#Extenção padrão da camada de interação com usuário front-end.
define('EXT_VIEW', '.html');

#Diretórios dos arquivos de template da view.
define('DIR_VIEW', ROOT . '/app/view');

#Criamos um constante chamada HOME que guarda automaticamente o endereço principal do site.
define('HOME', $SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);