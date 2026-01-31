let produtos = JSON.parse(localStorage.getItem("produtos")) || [];

// SALVAR PRODUTO
const form = document.getElementById("produtoForm");
if (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const id = document.getElementById("idProduto").value;
        const produto = {
            id: id ? id : Date.now(),
            nome: document.getElementById("nome").value,
            categoria: document.getElementById("categoria").value,
            preco: document.getElementById("preco").value,
            estoque: document.getElementById("estoque").value
        };

        if (id) {
            produtos = produtos.map(p => p.id == id ? produto : p);
        } else {
            produtos.push(produto);
        }

        localStorage.setItem("produtos", JSON.stringify(produtos));
        window.location.href = "produtos.html";
    });
}

// LISTAR PRODUTOS
function listarProdutos() {
    const tabela = document.getElementById("tabelaProdutos");
    if (!tabela) return;

    tabela.innerHTML = "";
    produtos.forEach(p => {
        tabela.innerHTML += `
            <tr>
                <td>${p.nome}</td>
                <td>${p.categoria}</td>
                <td>R$ ${parseFloat(p.preco).toFixed(2)}</td>
                <td>${p.estoque}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editar(${p.id})">Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="excluir(${p.id})">Excluir</button>
                </td>
            </tr>
        `;
    });
}
listarProdutos();

// EDITAR
function editar(id) {
    localStorage.setItem("editarProduto", id);
    window.location.href = "cadastro-produto.html";
}

// EXCLUIR
function excluir(id) {
    if (confirm("Deseja excluir este produto?")) {
        produtos = produtos.filter(p => p.id !== id);
        localStorage.setItem("produtos", JSON.stringify(produtos));
        listarProdutos();
    }
}

// CARREGAR PRODUTO PARA EDIÇÃO
const idEditar = localStorage.getItem("editarProduto");
if (idEditar && form) {
    const produto = produtos.find(p => p.id == idEditar);
    if (produto) {
        document.getElementById("idProduto").value = produto.id;
        document.getElementById("nome").value = produto.nome;
        document.getElementById("categoria").value = produto.categoria;
        document.getElementById("preco").value = produto.preco;
        document.getElementById("estoque").value = produto.estoque;
    }
    localStorage.removeItem("editarProduto");
}
