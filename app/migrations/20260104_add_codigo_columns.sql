-- Migration: adicionar colunas de recuperação de senha
ALTER TABLE usuario
  ADD COLUMN codigo_verificacao VARCHAR(10) NULL,
  ADD COLUMN codigo_gerado_em DATETIME NULL;

-- Obs: execute este arquivo com seu cliente SQL (mysql/postgres) apropriado.