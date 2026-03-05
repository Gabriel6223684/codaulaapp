<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'product' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Colunas da tabela product:\n";
    print_r($columns);
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
