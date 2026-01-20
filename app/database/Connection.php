<?php

namespace app\database;

use PDO;

class Connection
{
    #Variável de conexão com banco de dados.
    private static $pdo = null;
    #Método de conexão com banco de dados.
    public static function connection(): PDO
    {
        #Tentativa de estabelecer uma conexão com o banco de dados com tratamento de exceções.
        try {
            #Caso já exista a conexão com banco de dados retornamos a conexão.
            if (static::$pdo) {
                return static::$pdo;
            }
            # Definindo as opções para a conexão PDO.
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, # Lança exceções em caso de erros.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, # Define o modo de fetch padrão como array associativo.
                PDO::ATTR_EMULATE_PREPARES => false, # Desativa a emulação de prepared stratements.
                PDO::ATTR_PERSISTENT => true, # Conexão persistente para melhorar performance.
                PDO::ATTR_STRINGIFY_FETCHES => false, # Desativa a conversão de valores numéricos para strings.
            ];

            # Criação da nova conexão PDO com os parâmetros do banco de dados.
            static::$pdo = new PDO(
                'pgsql:host=localhost;port=5432;dbname=senac', # DSN (Data Source Name) para PostgreSQL.
                'gabriel', # Nome de usuário do banco de dados.
                '2009', # Senha do banco de dados.
                $options # Opções para a conexão PDO.
            );
            static::$pdo->exec("SET NAMES 'utf8'");
            #Caso seja bem-sucedida a conexão retornamos a variável $pdo;
            return static::$pdo;
        } catch (\PDOException $e) {
            error_log('[DB] Postgres connection failed: ' . $e->getMessage());
            // Fallback para SQLite (arquivo local) para permitir testes locais sem Postgres
            try {
                $dir = __DIR__ . '/../../data';
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $dbFile = $dir . '/dev.sqlite';
                static::$pdo = new PDO('sqlite:' . $dbFile);
                static::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                static::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Cria esquema mínimo se não existir
                static::$pdo->exec("CREATE TABLE IF NOT EXISTS usuario (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    nome TEXT,
                    sobrenome TEXT,
                    cpf TEXT,
                    rg TEXT,
                    data_nascimento TEXT,
                    senha TEXT,
                    ativo INTEGER DEFAULT 0,
                    administrador INTEGER DEFAULT 0,
                    codigo_verificacao TEXT,
                    codigo_gerado_em TEXT,
                    data_cadastro TEXT DEFAULT (datetime('now')),
                    data_alteracao TEXT DEFAULT (datetime('now'))
                );");

                static::$pdo->exec("CREATE TABLE IF NOT EXISTS contato (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    id_usuario INTEGER,
                    tipo TEXT,
                    contato TEXT,
                    data_cadastro TEXT,
                    data_alteracao TEXT
                );");

                static::$pdo->exec("CREATE TABLE IF NOT EXISTS verificacao_contato (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tipo TEXT NOT NULL,
                    contato TEXT NOT NULL,
                    codigo TEXT NOT NULL,
                    codigo_gerado_em TEXT NOT NULL,
                    usado INTEGER DEFAULT 0,
                    data_cadastro TEXT DEFAULT (datetime('now'))
                );");

                static::$pdo->exec("CREATE TABLE IF NOT EXISTS verificacao_tentativas (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tipo TEXT NOT NULL,
                    contato TEXT NOT NULL,
                    sucesso INTEGER NOT NULL DEFAULT 0,
                    criado_em TEXT DEFAULT (datetime('now'))
                );");

                // View equivalente para consultas (SQLite suporta CREATE VIEW)
                static::$pdo->exec("CREATE VIEW IF NOT EXISTS vw_usuario_contatos AS
                    SELECT u.id,
                        u.nome,
                        u.sobrenome,
                        u.cpf,
                        u.rg,
                        u.senha,
                        u.ativo,
                        u.administrador,
                        u.codigo_verificacao,
                        MAX(CASE WHEN c.tipo = 'email' THEN c.contato ELSE NULL END) AS email,
                        MAX(CASE WHEN c.tipo = 'celular' THEN c.contato ELSE NULL END) AS celular,
                        MAX(CASE WHEN c.tipo = 'whatsapp' THEN c.contato ELSE NULL END) AS whatsapp,
                        u.data_cadastro,
                        u.data_alteracao
                    FROM usuario u
                    LEFT JOIN contato c ON c.id_usuario = u.id
                    GROUP BY u.id, u.nome, u.sobrenome, u.cpf, u.rg, u.data_cadastro, u.data_alteracao;");

                return static::$pdo;
            } catch (\PDOException $e2) {
                error_log('[DB] Fallback SQLite failed: ' . $e2->getMessage());
                throw new \PDOException("Erro ao abrir fallback SQLite: " . $e2->getMessage(), 1);
            }
        }
    }
}
