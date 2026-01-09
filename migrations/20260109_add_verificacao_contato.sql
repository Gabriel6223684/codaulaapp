-- Migration: criar tabela para guardar códigos de verificação de contato
CREATE TABLE IF NOT EXISTS verificacao_contato (
    id bigserial PRIMARY KEY,
    tipo text NOT NULL, -- 'email' ou 'celular'
    contato text NOT NULL,
    codigo varchar(10) NOT NULL,
    codigo_gerado_em timestamp NOT NULL,
    usado boolean DEFAULT false,
    data_cadastro timestamp DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_verificacao_contato_tipo_contato ON verificacao_contato(tipo, contato);

-- Obs: execute este arquivo com seu cliente SQL (postgres) apropriado.
