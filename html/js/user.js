document.getElementById("cadastrar").addEventListener("click", function () {
    let nome = document.getElementById("nome").value.trim();
    let sobrenome = document.getElementById("sobrenome").value.trim();
    let cpfcnpj = document.getElementById("cpfcnpj").value.trim();
    let rg = document.getElementById("rg").value.trim();
    let ie = document.getElementById("ie").value.trim();
    let email = document.getElementById("email").value.trim();
    let senha = document.getElementById("senha").value.trim();

    let valido = true;

    // Limpa mensagens anteriores
    document.getElementById("erroNome").innerText = "";
    document.getElementById("erroSobrenome").innerText = "";
    document.getElementById("erroEmail").innerText = "";
    document.getElementById("erroSenha").innerText = "";

    // Validações básicas
    if (nome === "") {
        document.getElementById("erroNome").innerText = "Por favor, insira seu nome.";
        valido = false;
    }
    if (sobrenome === "") {
        document.getElementById("erroSobrenome").innerText = "Por favor, insira seu sobrenome.";
        valido = false;
    }
    if (email === "") {
        document.getElementById("erroEmail").innerText = "Por favor, insira seu e-mail.";
        valido = false;
    }
    if (senha === "" || senha.length < 6) {
        document.getElementById("erroSenha").innerText = "A senha deve ter pelo menos 6 caracteres.";
        valido = false;
    }

    if (!valido) return;

    let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

    let novoUsuario = {
        id: usuarios.length ? usuarios[usuarios.length - 1].id + 1 : 1,
        nome,
        sobrenome,
        cpfcnpj,
        rg,
        ie,
        email,
        senha
    };

    usuarios.push(novoUsuario);
    localStorage.setItem("usuarios", JSON.stringify(usuarios));

    alert("Usuário cadastrado com sucesso!");
    window.location.href = "/usuario/lista";
});

$('#cpfcnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
$('#telefone').inputmask({ 'mask': ['(99) 99 9 9999-9999'] });