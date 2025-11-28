import { Requests } from "./Requests.js";
import { Validate } from "./validate.js";

document.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById('cadastrar');
    console.log("Botão encontrado? ", btn);

    btn.addEventListener('click', async () => {
        
        const valido = Validate.SetForm('form').Validate();
        if (!valido) return;

        const response = await Requests.SetForm('form').Post('/cliente/insert');

        if (response.status) {
            window.location.href = "/cliente/lista";
        }
    });

});

// corrige CPF
$('#cpf_cnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
