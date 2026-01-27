import { Requests } from "./Requests.js";
import { Validate } from "./validate.js";

const Insert = document.getElementById('cadastrar');
//const CadastrarButton = document.getElementById('cadastrar');

$('#cpfcnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
$('#telefone').inputmask({ 'mask': ['(99) 99 9 9999-9999'] });

Insert.addEventListener('click', async () => {
    const response = Requests.SetForm('formCadastro').Post('/usuario/insert');
    console.log(response);
});

const formCadastro = document.getElementById("formCadastro");
let editarId = null;

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

// Inicializa a tabela ao carregar a página
carregarUsuarios();
