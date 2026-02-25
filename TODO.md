# TODO - Correções do Sistema

## Problema 1: Produto some após alteração ✅
- Arquivo: `public/js/product.js`
- Problema: Após salvar produto, redireciona para `/sale` ao invés de manter o produto visível
- Solução: Alterado o redirecionamento para `/product/lista`

## Problema 2: Condição de pagamento no select ✅
- Arquivo: `app/view/sale.html`
- Problema: Select de condição de pagamento deve aparecer abaixo do carrinho
- Solução: Verificar posicionamento no HTML (já está posicionado corretamente)

## Problema 3: Produtos somem ao atualizar a página ✅
- Arquivo: `public/js/sale.js`
- Problema: Carrinho é armazenado apenas em memória, perdendo dados ao atualizar
- Solução: Implementado persistência com localStorage:
  - saveCartToLocalStorage() - salva carrinho, desconto e juros
  - loadCartFromLocalStorage() - restaura carrinho ao carregar página
  - clearCartFromLocalStorage() - limpa dados ao finalizar/cancelar venda
  - Carrinho é salvo automaticamente ao adicionar produto, remover produto, alterar desconto ou juros
  - Carrinho é restaurado ao carregar a página se não houver ID de venda
