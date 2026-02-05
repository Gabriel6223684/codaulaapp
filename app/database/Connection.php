<?php

namespace app\database;

use PDO;
use PDOException;

class Connection
{
    private static $pdo = null;

    public static function connection(): PDO
    {
        try {
            if (self::$pdo !== null) {
                return self::$pdo;
            }

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$pdo = new PDO(
                'pgsql:host=localhost;port=5432;dbname=senac',
                'senac',
                'senac',
                $options
            );

            // Forma correta de definir encoding no PostgreSQL
            self::$pdo->exec("SET client_encoding TO 'UTF8'");

            return self::$pdo;

        } catch (PDOException $e) {
            die('Erro de conexão com o banco: ' . $e->getMessage());
        }
    }
}
