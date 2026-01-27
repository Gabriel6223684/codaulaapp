import { Requests } from "./Requests.js";
import { Validate } from "./validate.js";

const Cadastrar = document.getElementById('cadastrar');

// Cadastrar cliente
Cadastrar.addEventListener('click', async () => {
    const response = await Requests.SetForm('form').Post('/cliente/insert');
    console.log(response);

    if (response.status) {
        alert("Cliente cadastrado com sucesso!");
    } else {
        alert(response.msg);
    }
});

// corrige CPF
$('#cpf_cnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
