// ===============================
// LISTAGEM DE PRODUTOS
// ===============================

document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.querySelector("table tbody");
    const produtos = JSON.parse(localStorage.getItem("produtos")) || [];

    tbody.innerHTML = "";

    produtos.forEach((produto, index) => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${produto.codigo}</td>
            <td>${produto.fornecedor_id}</td>
            <td>${produto.codigo_barras}</td>
            <td>${produto.condicao_pagamento}</td>
            <td>${produto.nome_produto}</td>
            <td>${produto.descricao_curta}</td>
            <td>${produto.descricao}</td>
            <td>${produto.preco_custo}</td>
            <td>${produto.preco_venda}</td>
            <td>${produto.ativo}</td>
            <td>${produto.atualizado_em}</td>
            <td>${produto.criado_em}</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="excluirProduto(${index})">
                    Excluir
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
});

// ===============================
// EXCLUIR PRODUTO
// ===============================
function excluirProduto(index) {
    if (!confirm("Deseja realmente excluir este produto?")) return;

    let produtos = JSON.parse(localStorage.getItem("produtos")) || [];
    produtos.splice(index, 1);
    localStorage.setItem("produtos", JSON.stringify(produtos));

    location.reload();
}
