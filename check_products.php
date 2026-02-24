<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if products exist
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM product');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['cnt'] == 0) {
        // Insert some test products
        $stmt = $pdo->prepare("INSERT INTO product (nome, preco_venda, preco_custo, ativo, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute(['Produto Teste 1', 10.00, 5.00, true]);
        $stmt->execute(['Produto Teste 2', 20.00, 10.00, true]);
        $stmt->execute(['Produto Teste 3', 30.00, 15.00, true]);
        echo "Produtos de teste inseridos com sucesso!";
    } else {
        echo "Ja existem {$result['cnt']} produtos no banco.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
