<?php
require 'vendor/autoload.php';

$pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'postgres', 'postgres');
$pdo->exec("INSERT INTO customer (nome, cpf, created_at) VALUES ('Consumo Proprio', '000.000.000-00', NOW())");
echo 'Cliente inserido!';
