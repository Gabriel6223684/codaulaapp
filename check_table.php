<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('pgsql:host=postgres;dbname=development_db', 'senac', 'senac');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get table columns
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'customer' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Colunas da tabela customer:\n";
    print_r($columns);
    
    // Try to find customer name column
    echo "\nVerificando colunas que podem conter nome...\n";
    foreach ($columns as $col) {
        if (stripos($col, 'name') !== false || stripos($col, 'razao') !== false || stripos($col, 'fantas') !== false) {
            echo "Possivel coluna de nome encontrada: $col\n";
        }
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
