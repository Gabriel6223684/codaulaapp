-- Migration: criar tabela para registrar tentativas de confirmação de código
CREATE TABLE IF NOT EXISTS verificacao_tentativas (
    id bigserial PRIMARY KEY,
    tipo text NOT NULL, -- 'email' ou 'celular'
    contato text NOT NULL,
    sucesso boolean NOT NULL DEFAULT false,
    criado_em timestamp DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_verificacao_tentativas_contato ON verificacao_tentativas(tipo, contato, criado_em);

-- Obs: execute este arquivo com seu cliente SQL (postgres) apropriado.
