// ===============================
// CADASTRO DE PRODUTOS
// ===============================

document.addEventListener("DOMContentLoaded", () => {
    const btnSalvar = document.getElementById("insertPaymentoTermsButton");

    if (!btnSalvar) return;

    btnSalvar.addEventListener("click", () => {
        const produto = {
            codigo: document.getElementById("codigo")?.value || "",
            fornecedor_id: document.getElementById("fornecedor_id")?.value || "",
            codigo_barras: document.getElementById("codigo_barras")?.value || "",
            condicao_pagamento: document.getElementById("condicao_pagamento")?.value || "",
            nome_produto: document.getElementById("nome_produto")?.value || "",
            descricao_curta: document.getElementById("descricao_curta")?.value || "",
            descricao: document.getElementById("descricao")?.value || "",
            preco_custo: document.getElementById("preco_custo")?.value || "0",
            preco_venda: document.getElementById("preco_venda")?.value || "0",
            ativo: document.getElementById("ativo")?.value === "1" ? "Sim" : "Não",
            criado_em: new Date().toLocaleString(),
            atualizado_em: new Date().toLocaleString()
        };

        let produtos = JSON.parse(localStorage.getItem("produtos")) || [];
        produtos.push(produto);
        localStorage.setItem("produtos", JSON.stringify(produtos));

        alert("Produto cadastrado com sucesso!");

        window.location.href = "./listpaymentterms.html";
    });
});
