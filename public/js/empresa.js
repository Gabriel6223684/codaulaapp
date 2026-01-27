import { Requests } from "./Requests.js";
import { Validate } from "./validate.js";

const Cadastrar = document.getElementById('cadastrar');

// Máscaras
$('#cpfcnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
$('#telefone').inputmask({ 'mask': ['(99) 99999-9999'] });

// Cadastrar cliente
Cadastrar.addEventListener('click', async () => {
    const response = await Requests.SetForm('formCadastro').Post('/empresa/insert');
    console.log(response);

    if (response.status) {
        alert("Empresa cadastrado com sucesso!");
    } else {
        alert(response.msg);
    }
});
