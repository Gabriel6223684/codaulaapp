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
document.getElementById('prelogin')?.addEventListener('click', async function (e) {
    e.preventDefault();
    clearLoginError();

    const login = document.getElementById('loginEmail')?.value?.trim();
    const senha = document.getElementById('loginPassword')?.value?.trim();
    const remember = document.getElementById('rememberMe')?.checked || false;

    // Log dos dados
    console.log('Dados sendo enviados:', { login, senha, remember });

    // Validação básica
    if (!login || !senha) {
        showLoginError('E-mail/telefone e senha são obrigatórios');
        return;
    }

    try {
        // Enviando dados para o backend
        const payload = {
            login: login,
            senha: senha,
            remember: remember
        };

        console.log('Enviando fetch para /login com:', payload);

        const res = await fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });

        console.log('Resposta do servidor - Status:', res.status);

        const data = await res.json();
        console.log('Dados recebidos:', data);

        if (data.status) {
            showLoginSuccess(data.msg || 'Login realizado com sucesso!');
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        } else {
            showLoginError(data.msg || 'Erro ao fazer login');
        }
    } catch (err) {
        showLoginError('Erro de rede: ' + err.message);
        console.error('Erro ao fazer login:', err);
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

        console.log('Status da resposta:', response.status);

        if (response.status === 401) {
            showPrecadastroAlert("Não autorizado (401). Verifique configurações do servidor.", false);
            return;
        }

        let data;
        try {
            const ct = response.headers.get("content-type") || "";
            if (ct.includes("application/json")) {
                data = await response.json();
            } else {
                const txt = await response.text();
                showPrecadastroAlert("Resposta inválida do servidor: " + txt, false);
                console.error('Resposta não JSON:', txt);
                return;
            }
        } catch (err) {
            showPrecadastroAlert("Erro ao processar resposta: " + err.message, false);
            console.error('Erro ao parsear JSON:', err);
            return;
        }

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
