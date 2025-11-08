<?php

namespace app\database;

use PDO;

class Connection
{
    private static $pdo = null;
    public static function connection(): PDO
    {
        try {
            if (static::$pdo) {
                return static::$pdo;
            }
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ];

            static::$pdo = new PDO(
                'pgsql:host=localhost;port=5432;dbname=senac5',
                'senac5',
                'senac',
                $options
            );
            static::$pdo->exec("SET NAMES 'utf8'");
            return static::$pdo;
        } catch (\PDOException $e) {
            throw new \PDOException("Erro: " . $e->getMessage(), 1);
        }
    }
} 

$usuario = "senac5";
$senha = "senac";
$porta = "5432";
$host = "localhost";
$banco = "senac5";
$dsn = "pgsql:host=$host;port=$porta;dbname=$banco";
try{
    $connection = new PDO($dsn, $usuario, $senha);
    echo "Conexão estabelecida com sucesso!";
} catch (\PDOException $e) {
    echo"Restrição: " . $e->getMessage();
}
