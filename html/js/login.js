import { Validate } from "./Validate.js";
import { Requests } from "./Requests.js";

const Salvar = document.getElementById('salvar');

$('#cpf').inputmask({ "mask": ["999.999.999-99"] });
$('#celular').inputmask({ "mask": ["(99) 99999-9999"] });
$('#whatsapp').inputmask({ "mask": ["(99) 99999-9999"] });

Salvar.addEventListener('click', async () => {
    Validate.SetForm('form').Validate();
    const response = await Requests.SetForm('form').Post('/fornecedor/insert');
    console.log(response);
});

preCadastro.addEventListener('click', async () => {
    try {
        const response = await Requests.SetForm('form').Post('/login/precadastro');
        if(!response.status) {
            Swal.fire({   
            })
            Swal.fire({
            })
        }
    }
});