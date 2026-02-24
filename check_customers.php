<?php
require 'vendor/autoload.php';

$pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
$stmt = $pdo->query('SELECT id, nome FROM customer LIMIT 5');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo "Nenhum cliente encontrado!\n";
} else {
    echo "Clientes encontrados:\n";
    print_r($results);
}
