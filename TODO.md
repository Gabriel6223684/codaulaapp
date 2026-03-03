# TODO - Ajuste de Estoque

## Tarefas Concluídas:

### 1. app/controller/Product.php
- [x] Modificar método `adjustStock` para aceitar "novo_estoque" diretamente (substituir o estoque atual)

### 2. app/view/listproduct.html
- [x] Adicionar botão "Ajustar Estoque" na coluna de ações
- [x] Adicionar modal com campos: Estoque Anterior (readonly), Novo Estoque (input), Botão Salvar

### 3. public/js/listproduct.js
- [x] Adicionar função para abrir modal e carregar estoque atual
- [x] Adicionar função para salvar o novo estoque

### 4. app/database/migrations/20260223223206_stock_movement.php
- [x] Corrigido para usar tipos VARCHAR em vez de tipos PostgreSQL customizados

### 5. fix_stock_movement.sql
- [x] Script SQL para adicionar colunas faltantes na tabela (executar no banco de dados)

## Como usar:
1. Execute o script `fix_stock_movement.sql` no banco de dados PostgreSQL para adicionar as colunas `tipo` e `origem_movimento` na tabela `stock_movement`
2. Acesse a página de listagem de produtos
3. Clique no botão "Ajustar Estoque" para abrir o modal
4. Digite o novo valor do estoque e clique em Salvar
