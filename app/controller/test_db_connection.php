<?php
require 'vendor/autoload.php';

use app\database\Connection;

echo "<h2>Testando conexão com banco de dados...</h2>";

try {
    $pdo = Connection::connection();
    echo "<p style='color: green;'>✓ Conexão estabelecida com sucesso!</p>";
    
    // Testar uma query simples
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Query teste: " . print_r($result, true) . "</p>";
    
    // Listar tabelas
    echo "<h3>Tabelas existentes:</h3>";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p>Nenhuma tabela encontrada.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    // Verificar se tabela stock_movement existe
    echo "<h3>Verificando tabela stock_movement:</h3>";
    if (in_array('stock_movement', $tables)) {
        echo "<p style='color: green;'>✓ Tabela stock_movement existe!</p>";
        
        $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'stock_movement' ORDER BY ordinal_position");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>{$col['column_name']} - {$col['data_type']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Tabela stock_movement NÃO existe!</p>";
    }
    
} catch (\PDOException $e) {
    echo "<p style='color: red;'>✗ Erro na conexão:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    echo "<h3>Possíveis soluções:</h3>";
    echo "<ol>";
    echo "<li>Verifique se o Docker está rodando: <code>docker ps</code></li>";
    echo "<li>Inicie os containers: <code>docker-compose up -d</code></li>";
    echo "<li>Verifique os logs: <code>docker-compose logs postgres</code></li>";
    echo "</ol>";
}
