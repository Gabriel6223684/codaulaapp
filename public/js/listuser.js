import { DataTables } from "./DataTables.js";

DataTables.SetId('tabela').Post('/user/searchuser');

async function editarUsuario(id) {
    const res = await fetch("/userController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=list`
    });
    const usuarios = await res.json();
    const user = usuarios.find(u => u.id == id);
    if (user) {
        document.getElementById("nome").value = user.nome;
        document.getElementById("cpfcnpj").value = user.cpfcnpj;
        document.getElementById("email").value = user.email;
        document.getElementById("senha").value = user.senha;
        editarId = id;
    }
}

async function excluirUsuario(id) {
    if (!confirm("Deseja realmente excluir este usuário?")) return;
    await fetch("/userController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&id=${id}`
    });
    carregarUsuarios();
}