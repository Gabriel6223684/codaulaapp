<<<<<<< HEAD
// Helpers para mensagens
function showLoginError(msg) {
    const el = document.getElementById('loginError');
    if (!el) return;
=======
// Helpers para exibir mensagens inline (evita uso de alert que interrompe o fluxo)
function showLoginError(msg) {
    const el = document.getElementById('loginError');
    if (!el) { console.error('loginError element not found:', msg); return; }
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success', 'alert-danger');
    el.classList.add('alert-danger');
}
<<<<<<< HEAD

function showLoginSuccess(msg) {
    const el = document.getElementById('loginError');
    if (!el) return;
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
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success', 'alert-danger');
    el.classList.add(success ? 'alert-success' : 'alert-danger');
}

// ---------------------- LOGIN ----------------------
document.getElementById("prelogin").addEventListener("click", async () => {
    clearLoginError();

    const form = new FormData(document.getElementById("formlogin"));

    let res;
    try {
        res = await fetch("/login/autenticar", {
            method: "POST",
            body: form,
            credentials: 'include', // ✅ envia cookies de sessão
            redirect: 'manual' // ✅ captura redirect
        });
    } catch (err) {
        showLoginError('Erro de rede: ' + err.message);
        return;
    }

    // redirecionamento manual do Slim
    if (res.status === 302 || res.status === 301) {
        const redirectUrl = res.headers.get('Location');
        if (redirectUrl) {
            window.location.href = redirectUrl;
            return;
        }
    }

    let json;
    try {
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
            json = await res.json();
        } else {
            const txt = await res.text();
            showLoginError('Resposta inesperada do servidor: ' + txt);
            return;
        }
=======
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
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
    } catch (err) {
        showLoginError('Erro ao processar resposta do servidor: ' + err.message);
        return;
    }

    if (json.status) {
<<<<<<< HEAD
        showLoginSuccess(json.msg || 'Login realizado com sucesso');
        setTimeout(() => {
            window.location.href = "/dashboard"; // ou página inicial
        }, 500);
    } else {
        showLoginError(json.msg || 'Usuário ou senha inválidos');
    }
});

// ------------------- PRÉ-CADASTRO -------------------
=======
        // Mostrar mensagem de sucesso e redirecionar
        showLoginSuccess(json.msg || 'Login realizado com sucesso');
        window.location.href = "/dashboard";
    } else {
        showLoginError(json.msg || json.message || 'Usuário ou senha inválidos');
    }
});

// Pré-cadastro
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
document.getElementById("buttaoPrecadastro").addEventListener("click", async () => {
    const nome = document.getElementById("nome").value;
    const email = document.getElementById("email").value;
    const telefone = document.getElementById("telefone").value;
    const whatsapp = document.getElementById("whatsapp").value;
    const senha = document.getElementById("senha").value;

<<<<<<< HEAD
=======
    // Limpa alert
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
    showPrecadastroAlert('', true);

    let response;
    try {
        response = await fetch("/login/precadastro", {
            method: "POST",
<<<<<<< HEAD
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nome, email, telefone, whatsapp, senhaCadastro: senha }),
            credentials: 'include', // ✅ mantém sessão
            redirect: 'manual'
=======
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                nome,
                email,
                telefone,
                whatsapp,
                senhaCadastro: senha
            }),
            redirect: 'follow'
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
        });
    } catch (err) {
        showPrecadastroAlert('Erro de rede: ' + err.message, false);
        return;
    }

<<<<<<< HEAD
    if (response.status === 302 || response.status === 301) {
        const url = response.headers.get('Location');
        if (url) {
            window.location.href = url;
            return;
        }
=======
    if (response.status === 401) {
        const txt = await response.text();
        console.error('Resposta 401 do servidor (precadastro):', txt || response.status);
        showPrecadastroAlert('Não autorizado (401). Verifique configurações do servidor e tente novamente.', false);
        return;
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
    }

    let data;
    try {
        const ct = response.headers.get('content-type') || '';
<<<<<<< HEAD
        if (ct.includes('application/json')) {
            data = await response.json();
        } else {
            const txt = await response.text();
            showPrecadastroAlert('Resposta inválida do servidor: ' + txt, false);
=======
        if (ct.indexOf('application/json') !== -1) {
            data = await response.json();
        } else {
            const txt = await response.text();
            showPrecadastroAlert('Resposta inválida do servidor: ' + (txt || `status ${response.status}`), false);
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
            return;
        }
    } catch (err) {
        showPrecadastroAlert('Erro ao processar resposta do servidor: ' + err.message, false);
        return;
    }

    if (data.status) {
<<<<<<< HEAD
        showPrecadastroAlert(data.msg || 'Cadastro realizado com sucesso', true);
        const modalEl = document.getElementById('modalPreCadastro');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        setTimeout(() => modal.hide(), 800);
        document.getElementById('precadastro').reset();
    } else {
        showPrecadastroAlert(data.msg || 'Erro no cadastro', false);
    }
});
=======
        showPrecadastroAlert(data.msg || "Cadastro realizado com sucesso!", true);
        const modalEl = document.getElementById('modalPreCadastro');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        // Esconde o modal após uma pequena pausa para o usuário ver a mensagem
        setTimeout(() => modal.hide(), 800);
        // limpa campos
        document.getElementById('precadastro').reset();
    } else {
        showPrecadastroAlert(data.msg || "Erro no cadastro", false);
    }
});
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
