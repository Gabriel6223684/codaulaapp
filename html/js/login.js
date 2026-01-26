import { Requests } from "./Requests.js";
const Login = document.getElementById('prelogin');

// ========== FUNÇÕES DE EXIBIÇÃO DE MENSAGENS ==========
function showLoginError(msg) {
    const el = document.getElementById('loginError');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success');
    el.classList.add('alert-danger');
}

function showLoginSuccess(msg) {
    const el = document.getElementById('loginError');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-danger');
    el.classList.add('alert-success');
}

function clearLoginError() {
    const el = document.getElementById('loginError');
    if (el) {
        el.classList.add('d-none');
        el.textContent = '';
    }
}

// ========== EVENTO DO BOTÃO "ENTRAR" ==========
Login.addEventListener('click', async () => {
    try {
        const response = await Requests.SetForm('formlogin').Post('/login/autenticar');

    } catch (error) {
    }
});

function showPrecadastroAlert(msg, success) {
    const el = document.getElementById('precadastroAlert');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success', 'alert-danger');
    el.classList.add(success ? 'alert-success' : 'alert-danger');
}

// ================= PRÉ-CADASTRO =================

document.getElementById("buttaoPrecadastro")?.addEventListener("click", async () => {
    const nome = document.getElementById("nome")?.value || '';
    const email = document.getElementById("email")?.value || '';
    const cpf = document.getElementById("cpf")?.value || '';
    const telefone = document.getElementById("telefone")?.value || '';
    const whatsapp = document.getElementById("whatsapp")?.value || '';
    const senha = document.getElementById("senha")?.value || '';

    // Limpar alerta anterior
    showPrecadastroAlert("", true);

    // Validações básicas - APENAS OBRIGATÓRIOS
    if (!nome.trim()) {
        showPrecadastroAlert('Nome é obrigatório', false);
        return;
    }
    if (!email.trim()) {
        showPrecadastroAlert('E-mail é obrigatório', false);
        return;
    }
    if (!senha.trim()) {
        showPrecadastroAlert('Senha é obrigatória', false);
        return;
    }

    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.trim())) {
        showPrecadastroAlert('E-mail inválido', false);
        return;
    }

    console.log('Enviando pré-cadastro:', { nome, email, cpf, telefone, senha: '***' });

    try {
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

        let data;

        console.log('Dados recebidos:', data);

        if (data.status) {
            showPrecadastroAlert(data.msg || "Cadastro realizado com sucesso!", true);
            const modalEl = document.getElementById("modalPreCadastro");
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                setTimeout(() => {
                    modal.hide();
                    // Limpar formulário
                    document.getElementById("precadastro")?.reset();
                }, 1500);
            }
        } else {
            showPrecadastroAlert(data.msg || "Erro no cadastro", false);
        }
    } catch (err) {
        showPrecadastroAlert("Erro de rede: " + err.message, false);
        console.error('Erro ao enviar pré-cadastro:', err);
    }
});
