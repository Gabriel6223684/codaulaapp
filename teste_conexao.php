<?php

// Teste de conexão com o banco de dados
require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "Testando conexão com PostgreSQL...\n";

    // Dados da conexão
    $host = 'localhost';
    $port = '5432';
    $dbname = 'senac';
    $user = 'gabriel';
    $password = '2009';

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

    echo "DSN: {$dsn}\n";
    echo "Usuário: {$user}\n";
    echo "Banco: {$dbname}\n\n";

    // Tentar conexão
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ CONEXÃO SUCESSO!\n\n";

    // Testar query
    $stmt = $pdo->prepare("SELECT version();");
    $stmt->execute();
    $result = $stmt->fetch();

    echo "Versão PostgreSQL: " . $result['version'] . "\n\n";

    // Listar tabelas
    $stmt = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;");
    $stmt->execute();
    $tables = $stmt->fetchAll();

    echo "Tabelas no banco 'senac':\n";
    foreach ($tables as $table) {
        echo "  ✓ " . $table['table_name'] . "\n";
    }

    // Listar usuários
    echo "\nUsuários cadastrados:\n";
    $stmt = $pdo->prepare("SELECT id, nome, email, ativo FROM usuario ORDER BY id;");
    $stmt->execute();
    $users = $stmt->fetchAll();

    if (empty($users)) {
        echo "  (nenhum usuário)\n";
    } else {
        foreach ($users as $user) {
            $status = $user['ativo'] ? '✓ ativo' : '✗ inativo';
            echo "  ID {$user['id']} - {$user['nome']} ({$user['email']}) - $status\n";
        }
    }
} catch (\PDOException $e) {
    echo "❌ ERRO DE CONEXÃO:\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    exit(1);
}
