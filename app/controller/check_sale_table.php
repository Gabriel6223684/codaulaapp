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
    
    // Check products
    $stmt = $pdo->query('SELECT id, nome, preco_venda FROM product LIMIT 5');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nProdutos:\n";
    print_r($products);
    
    // Check customer
    $stmt = $pdo->query('SELECT id, nome_fantasia FROM customer LIMIT 5');
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nClientes:\n";
    print_r($customers);
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
