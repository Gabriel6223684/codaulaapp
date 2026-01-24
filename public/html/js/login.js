import { Requests } from '/public/html/js/modules/Requests.js';
import {
    showLoginError,
    clearLoginError,
    showLoginSuccess
} from '/public/html/js/modules/LoginMessages.js';

const Login = document.getElementById('prelogin');

Login?.addEventListener('click', async () => {
    try {
        clearLoginError();
        const response = await Requests.SetForm('formlogin')
            .Post('/login/autenticar');

        if (response?.status) {
            showLoginSuccess(response.msg || 'Login realizado!');
            window.location.href = '/';
        } else {
            showLoginError(response?.msg || 'Usuário ou senha inválidos');
        }

    } catch (error) {
        showLoginError('Erro ao tentar login');
        console.error(error);
    }
});

const response = await fetch("/login/precadastro", {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({
        nome: nome.trim(),
        email: email.trim(),
        cpf: cpf.trim(),
        telefone: telefone.trim(),
        whatsapp: whatsapp.trim(),
        senhaCadastro: senha.trim()
    })
});

const data = await response.json(); // 🔥 ISSO FALTAVA

console.log('Dados recebidos:', data);

if (data.status) {
    showPrecadastroAlert(data.msg || "Cadastro realizado com sucesso!", true);

    const modalEl = document.getElementById("modalPreCadastro");
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl)
            || new bootstrap.Modal(modalEl);

        setTimeout(() => {
            modal.hide();
            document.getElementById("precadastro")?.reset();
        }, 1500);
    }
} else {
    showPrecadastroAlert(data.msg || "Erro no cadastro", false);
}

const autenticar = document.getElementById('autenticar');

autenticar?.addEventListener('click', async () => {
    try {
        clearLoginError();
        const response = await Requests.SetForm('formlogin')
            .Post('/login/autenticar');
        if (response?.status) {
            showLoginSuccess(response.msg || 'Login realizado!');
            window.location.href = '/';
        } else {
            showLoginError(response?.msg || 'Usuário ou senha inválidos');
        }
    } catch (error) {
        showLoginError('Erro ao tentar login');
        console.error(error);
    }
});

const precadastro = document.getElementById('precadastro');

precadastro?.addEventListener('click', async () => {
    try {
        clearPrecadastroAlert();
        const response = await Requests.SetForm('precadastro')
            .Post('/login/precadastro');
        if (response?.status) {
            showPrecadastroAlert(response.msg || "Cadastro realizado com sucesso!", true);
            const modalEl = document.getElementById("modalPreCadastro");
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl)
                    || new bootstrap.Modal(modalEl);
                setTimeout(() => {
                    modal.hide();
                    document.getElementById("precadastro")?.reset();
                }, 1500);
            }
        } else {
            showPrecadastroAlert(response?.msg || "Erro no cadastro", false);
        }
    } catch (error) {
        showPrecadastroAlert("Erro ao tentar cadastrar", false);
        console.error(error);
    }
});

const prelogin = {
    nome: 'nome',
    email: 'email',
    cpf: 'cpf',
    telefone: 'telefone',
    whatsapp: 'whatsapp',
    senha: 'senha'
}
export { prelogin };