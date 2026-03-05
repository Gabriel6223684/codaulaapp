<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if customer exists
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM customer');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['cnt'] == 0) {
        // Insert a customer with correct column names
        $stmt = $pdo->prepare("INSERT INTO customer (nome_fantasia, sobrenome_razao, cpf_cnpj, ativo, data_cadastro) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute(['Consumidor Final', 'Consumidor Final', '000.000.000-00', true]);
        echo "Cliente inserido com sucesso!";
    } else {
        echo "Ja existem {$result['cnt']} clientes no banco.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
