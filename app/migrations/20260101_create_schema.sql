-- Banco de dados SENAC - Script de inicialização
-- PostgreSQL 16

-- Habilitar extensão UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuario (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    celular VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    administrador BOOLEAN DEFAULT FALSE,
    codigo_verificacao VARCHAR(10),
    codigo_gerado_em TIMESTAMP,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de verificação de contatos
CREATE TABLE IF NOT EXISTS verificacao_contato (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'email' ou 'celular'
    contato VARCHAR(255) NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    codigo_gerado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de tentativas de verificação
CREATE TABLE IF NOT EXISTS verificacao_tentativas (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    contato VARCHAR(255) NOT NULL,
    sucesso BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- View para usuário com contatos
CREATE OR REPLACE VIEW vw_usuario_contatos AS
SELECT 
    u.id,
    u.nome,
    u.email,
    u.cpf,
    u.celular,
    u.senha,
    u.ativo,
    u.administrador,
    u.codigo_verificacao,
    u.codigo_gerado_em,
    u.criado_em,
    u.atualizado_em
FROM usuario u;

-- Índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_usuario_email ON usuario(email);
CREATE INDEX IF NOT EXISTS idx_usuario_cpf ON usuario(cpf);
CREATE INDEX IF NOT EXISTS idx_usuario_celular ON usuario(celular);
CREATE INDEX IF NOT EXISTS idx_verificacao_contato_contato ON verificacao_contato(contato);
CREATE INDEX IF NOT EXISTS idx_verificacao_tentativas_contato ON verificacao_tentativas(contato);

-- Dados de teste (comentado - descomente se necessário)
-- INSERT INTO usuario (nome, email, cpf, celular, senha, ativo, administrador) 
-- VALUES ('Admin', 'admin@test.com', '12345678901', '11999999999', '$2y$10$...', TRUE, TRUE);
