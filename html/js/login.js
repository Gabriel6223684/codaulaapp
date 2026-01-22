// Helpers para exibir mensagens inline (evita uso de alert que interrompe o fluxo)
function showLoginError(msg) {
    const el = document.getElementById('loginError');
    if (!el) { console.error('loginError element not found:', msg); return; }
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success', 'alert-danger');
    el.classList.add('alert-danger');
}
function showLoginSuccess(msg) {
    const el = document.getElementById('loginError');
    if (!el) { return; }
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-danger');
    el.classList.add('alert-success');
}
function clearLoginError() {
    const el = document.getElementById('loginError');
    if (el) { el.classList.add('d-none'); el.textContent = ''; }
}

function showPrecadastroAlert(msg, success) {
    const el = document.getElementById('precadastroAlert');
    if (!el) { console.error('precadastroAlert not found:', msg); return; }
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success', 'alert-danger');
    el.classList.add(success ? 'alert-success' : 'alert-danger');
}

// Formulário de login
document.getElementById("formlogin").addEventListener("submit", async (e) => {
    e.preventDefault();
    clearLoginError();

    const form = new FormData(e.target);

    let res;
    try {
        res = await fetch("/login", {
            method: "POST",
            body: form,
            redirect: 'follow'
        });
    } catch (err) {
        showLoginError('Erro de rede: ' + err.message);
        return;
    }

    if (res.status === 401) {
        const txt = await res.text();
        console.error('Resposta 401 do servidor:', txt || res.status);
        showLoginError('Não autorizado (401). Verifique as credenciais.');
        return;
    }

    let json;
    try {
        const ct = res.headers.get('content-type') || '';
        if (ct.indexOf('application/json') !== -1) {
            json = await res.json();
        } else {
            const txt = await res.text();
            // Se o servidor retornou HTML (por ex. foi redirecionado para o login), mostramos uma mensagem amigável
            if (res.redirected || /<html|doctype/i.test(txt)) {
                showLoginError('Resposta inesperada: servidor retornou HTML. Verifique os logs do servidor.');
            } else {
                showLoginError('Resposta inválida do servidor: ' + (txt || `status ${res.status}`));
            }
            return;
        }
    } catch (err) {
        showLoginError('Erro ao processar resposta do servidor: ' + err.message);
        return;
    }

    if (json.status) {
        // Mostrar mensagem de sucesso e redirecionar
        showLoginSuccess(json.msg || 'Login realizado com sucesso');
        window.location.href = "/dashboard";
    } else {
        showLoginError(json.msg || json.message || 'Usuário ou senha inválidos');
    }
});

// Função auxiliar para enviar dados para o servidor
async function enviarDadosParaServidor(endpoint, dados, elementoAlerta) {
    showPrecadastroAlert('', true);

    try {
        const response = await fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(dados),
            redirect: 'follow'
        });

        if (response.status === 401) {
            const txt = await response.text();
            console.error(`Resposta 401 do servidor (${endpoint}):`, txt || response.status);
            showPrecadastroAlert('Não autorizado (401). Verifique configurações do servidor.', false);
            return null;
        }

        const ct = response.headers.get('content-type') || '';
        if (ct.indexOf('application/json') !== -1) {
            return await response.json();
        } else {
            const txt = await response.text();
            showPrecadastroAlert('Resposta inválida do servidor: ' + (txt || `status ${response.status}`), false);
            return null;
        }
    } catch (err) {
        showPrecadastroAlert('Erro de rede: ' + err.message, false);
        return null;
    }
}

// Pré-cadastro
document.getElementById("buttaoPrecadastro").addEventListener("click", async () => {
    const nome = document.getElementById("nome")?.value || '';
    const email = document.getElementById("email")?.value || '';
    const telefone = document.getElementById("telefone")?.value || '';
    const whatsapp = document.getElementById("whatsapp")?.value || '';
    const senha = document.getElementById("senha")?.value || '';

    // Validações básicas
    if (!nome.trim()) {
        showPrecadastroAlert('Nome é obrigatório', false);
        return;
    }
    if (!email.trim()) {
        showPrecadastroAlert('E-mail é obrigatório', false);
        return;
    }
    if (!telefone.trim()) {
        showPrecadastroAlert('Telefone é obrigatório', false);
        return;
    }
    if (!senha.trim()) {
        showPrecadastroAlert('Senha é obrigatória', false);
        return;
    }

    const dados = {
        nome: nome.trim(),
        email: email.trim(),
        telefone: telefone.trim(),
        whatsapp: whatsapp.trim(),
        senhaCadastro: senha.trim()
    };

    const resposta = await enviarDadosParaServidor("/login/precadastro", dados);

    if (resposta && resposta.status) {
        showPrecadastroAlert(resposta.msg || "Cadastro realizado com sucesso!", true);
        const modalEl = document.getElementById('modalPreCadastro');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        // Esconde o modal após uma pequena pausa para o usuário ver a mensagem
        setTimeout(() => modal.hide(), 1500);
        // limpa campos
        document.getElementById('precadastro').reset();
    } else if (resposta) {
        showPrecadastroAlert(resposta.msg || "Erro no cadastro", false);
    }
});