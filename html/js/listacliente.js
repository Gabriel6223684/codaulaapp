const tabelaClientes = new $('#tabela').DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    stateSave: true,
    select: true,
    processing: true,
    serverSide: true,
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json',
        searchPlaceholder: 'Digite sua pesquisa...'
    },
    ajax: {
        url: '/cliente/listacliente',
        type: 'POST'
    }
});


import { Requests } from "./Requests.js";

// ELEMENTOS
const tabela = document.querySelector("#tabela tbody");

// =====================================================================
// CARREGAR LISTA
// =====================================================================
async function carregarLista() {
    const response = await Requests.SetForm('form').Post('/cliente/listacliente');

    tabela.innerHTML = ""; 

    response.forEach(cliente => {
        tabela.innerHTML += `
            <tr>
                <td>${cliente.id}</td>
                <td>${cliente.nome}</td>
                <td>${cliente.cpf_cnpj}</td>
                <td>${cliente.email}</td>
                <td>${cliente.senha}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="editarCliente(${cliente.id}, '${cliente.nome}', '${cliente.cpf_cnpj}', '${cliente.email}', '${cliente.senha}')">
                        Editar
                    </button>

                    <button class="btn btn-danger btn-sm" onclick="confirmarExcluir(${cliente.id})">
                        Excluir
                    </button>
                </td>
            </tr>
        `;
    });
}

// =====================================================================
// EDITAR CLIENTE
// =====================================================================
window.editarCliente = (id, nome, cpf, email, senha) => {
    document.getElementById("editId").value = id;
    document.getElementById("editNome").value = nome;
    document.getElementById("editCpfcnpj").value = cpf;
    document.getElementById("editEmail").value = email;
    document.getElementById("editSenha").value = senha;

    new bootstrap.Modal(document.getElementById("modalEditar")).show();
};

document.getElementById("salvarEdicao").addEventListener("click", async () => {
    const formData = new FormData();
    formData.append("id", document.getElementById("editId").value);
    formData.append("nome", document.getElementById("editNome").value);
    formData.append("cpf_cnpj", document.getElementById("editCpfcnpj").value);
    formData.append("email", document.getElementById("editEmail").value);
    formData.append("senha", document.getElementById("editSenha").value);

    const response = await fetch("/cliente/editar", { method: "POST", body: formData });
    await response.json();

    carregarLista();
    bootstrap.Modal.getInstance(document.getElementById("modalEditar")).hide();
});

// =====================================================================
// EXCLUIR CLIENTE
// =====================================================================
let idExcluir = 0;

window.confirmarExcluir = (id) => {
    idExcluir = id;
    new bootstrap.Modal(document.getElementById("modalExcluir")).show();
};

document.getElementById("confirmarExclusao").addEventListener("click", async () => {
    await fetch(`/cliente/excluir/${idExcluir}`, { method: "DELETE" });
    carregarLista();
    bootstrap.Modal.getInstance(document.getElementById("modalExcluir")).hide();
});

// =====================================================================
// CARREGAR AO ABRIR A PÁGINA
// =====================================================================
document.addEventListener("DOMContentLoaded", carregarLista);
