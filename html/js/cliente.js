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
    document.getElementById("erroCpfCnpj").innerText = "";
    document.getElementById("erroRg").innerText = "";
    document.getElementById("erroIe").innerText = "";

    // Validações básicas já existentes
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

    // ==============================
    // Validação CPF/CNPJ, RG e IE
    // ==============================
    function apenasNumeros(valor) {
        return valor.replace(/\D/g, "");
    }

    // CPF: 11 dígitos / CNPJ: 14 dígitos
    let cpfCnpjNumeros = apenasNumeros(cpfcnpj);
    if (cpfCnpjNumeros.length !== 11 && cpfCnpjNumeros.length !== 14) {
        document.getElementById("erroCpfCnpj").innerText = "CPF deve ter 11 dígitos ou CNPJ 14 dígitos.";
        valido = false;
    }

    // RG simples: pelo menos 6 dígitos numéricos
    let rgNumeros = apenasNumeros(rg);
    if (rgNumeros.length < 6) {
        document.getElementById("erroRg").innerText = "RG inválido.";
        valido = false;
    }

    // IE simples: pelo menos 8 dígitos
    let ieNumeros = apenasNumeros(ie);
    if (ieNumeros.length < 8) {
        document.getElementById("erroIe").innerText = "Inscrição Estadual inválida.";
        valido = false;
    }

    if (!valido) return;

    // Grava no localStorage
    let clientes = JSON.parse(localStorage.getItem("clientes")) || [];

    let novoCliente = {
        id: clientes.length ? clientes[clientes.length - 1].id + 1 : 1,
        nome,
        sobrenome,
        cpfcnpj,
        rg,
        ie,
        email,
        senha
    };

    clientes.push(novoCliente);
    localStorage.setItem("clientes", JSON.stringify(clientes));

    alert("Cliente cadastrado com sucesso!");
    window.location.href = "/cliente/lista";
});

$('#cpfcnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
$('#telefone').inputmask({ 'mask': ['(99) 99 9 9999-9999'] });