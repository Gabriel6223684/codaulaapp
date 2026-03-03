-- Script para corrigir a tabela stock_movement
-- Adiciona as colunas tipo e origem_movimento caso não existam

-- Adicionar coluna tipo se não existir
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'stock_movement' AND column_name = 'tipo'
    ) THEN
        ALTER TABLE stock_movement ADD COLUMN tipo VARCHAR(50) DEFAULT 'entrada';
        RAISE NOTICE 'Coluna tipo adicionada com sucesso!';
    ELSE
        RAISE NOTICE 'Coluna tipo ja existe.';
    END IF;
END $$;

-- Adicionar coluna origem_movimento se não existir
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'stock_movement' AND column_name = 'origem_movimento'
    ) THEN
        ALTER TABLE stock_movement ADD COLUMN origem_movimento VARCHAR(50) DEFAULT 'ajuste';
        RAISE NOTICE 'Coluna origem_movimento adicionada com sucesso!';
    ELSE
        RAISE NOTICE 'Coluna origem_movimento ja existe.';
    END IF;
END $$;

SELECT 'Correcao concluida! Tabela stock_movement atualizada.' AS resultado;
