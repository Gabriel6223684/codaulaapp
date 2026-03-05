<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate sale table to match migration
    $pdo->exec("DROP TABLE IF EXISTS item_sale CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS sale CASCADE");
    
    // Create sale table with correct columns
    $pdo->exec("
        CREATE TABLE sale (
            id BIGSERIAL PRIMARY KEY,
            id_cliente BIGINT REFERENCES customer(id) ON DELETE CASCADE,
            id_usuario BIGINT REFERENCES users(id) ON DELETE CASCADE,
            total_bruto DECIMAL(18,4),
            total_liquido DECIMAL(18,4),
            desconto DECIMAL(18,4),
            acrescimo DECIMAL(18,4),
            observacao TEXT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "Tabela sale recriada com sucesso!\n";
    
    // Create item_sale table
    $pdo->exec("
        CREATE TABLE item_sale (
            id BIGSERIAL PRIMARY KEY,
            id_venda BIGINT REFERENCES sale(id) ON DELETE CASCADE,
            id_produto BIGINT REFERENCES product(id) ON DELETE CASCADE,
            quantidade DECIMAL(18,4),
            preco_unitario DECIMAL(18,4),
            desconto DECIMAL(18,4),
            acrescimo DECIMAL(18,4),
            total DECIMAL(18,4),
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "Tabela item_sale criada com sucesso!\n";
    
    // Verify tables
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nTabelas no banco:\n";
    print_r($tables);
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
