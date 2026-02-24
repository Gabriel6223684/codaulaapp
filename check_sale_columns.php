<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get sale table columns
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'sale' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Colunas da tabela sale:\n";
    print_r($columns);
    
    // Get item_sale table columns
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'item_sale' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nColunas da tabela item_sale:\n";
    print_r($columns);
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
