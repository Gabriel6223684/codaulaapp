import { Requests } from "./Requests.js";
import { Validate } from "./validate.js";

const Insert = document.getElementById('cadastrar');
const CadastrarButton = document.getElementById('cadastrar');

$('#cpfcnpj').inputmask({ 'mask': ['999.999.999-99', '99.999.999/9999-99'] });
$('#telefone').inputmask({ 'mask': ['(99) 99 9 9999-9999'] });

Insert.addEventListener('click', async () => {
    const response = Requests.SetForm('formCadastro').Post('/usuario/insert');
    console.log(response);
});

CadastrarButton.addEventListener('click', async () => {
    const IsValid = Validate
        .SetForm('formCadastro')
        .Validate();
    Requests.SetForm('formCadastro').Post('/usuario/insert');
});