import { Requests } from "./Requests.js";
import { Validate } from "./validate.js";

// Máscaras
$('#cpfcnpj').inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'] });
$('#telefone').inputmask({ mask: ['(99) 9 9999-9999'] });

const salvaruser = document.getElementById("cadastraruser");

salvaruser.addEventListener("click", async () => {
    const response = await Requests.SetForm('formCadastro').Post('/usuario/insert');
    console.log(response);
});