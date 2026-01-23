<?php

namespace app\database;

use PDO;
use Exception;

class Connection
{
    // Variável de conexão com banco de dados
    private static $pdo = null;

    // Método de conexão com banco de dados
    public static function connection(): PDO
    {
        try {
            // Retorna a conexão existente se disponível
            if (static::$pdo) {
                return static::$pdo;
            }

            // Definindo as opções para a conexão PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ];

            // Carregando variáveis de ambiente para a conexão
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'senac';
            $user = getenv('DB_USER') ?: 'senac';
            $password = getenv('DB_PASSWORD') ?: 'senac';

            // Criando a DSN para PostgreSQL
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

            // Criação da nova conexão PDO
            static::$pdo = new PDO($dsn, $user, $password, $options);
            static::$pdo->exec("SET NAMES 'utf8'");

            return static::$pdo;
        } catch (\PDOException $e) {
            error_log('[DB] Postgres connection failed: ' . $e->getMessage());
            throw new Exception('Erro na conexão com banco de dados: ' . $e->getMessage());
        }
    }
}
