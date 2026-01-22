document.getElementById('formlogin').addEventListener('submit', async function(e) {
    e.preventDefault(); // previne reload
    const login = document.getElementById('loginEmail').value;
    const senha = document.getElementById('loginPassword').value;

    try {
        const res = await fetch('/login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({login, senha})
        });
        const data = await res.json();
        console.log(data);
    } catch(err) {
        console.error(err);
    }
});

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

function showPrecadastroAlert(msg, success) {
    const el = document.getElementById('precadastroAlert');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('d-none', 'alert-success', 'alert-danger');
    el.classList.add(success ? 'alert-success' : 'alert-danger');
}

// Envio do formulário de login
document.getElementById("formlogin")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearLoginError();

    const form = new FormData(e.target);

    let res;
    try {
        res = await fetch("/login/autenticar", {
            method: "POST",
            body: form,
            credentials: "include",
            redirect: "manual"
        });
    } catch (err) {
        showLoginError("Erro de rede: " + err.message);
        return;
    }

    // Redirect manual (Slim)
    if (res.status === 301 || res.status === 302) {
        const url = res.headers.get("Location");
        if (url) {
            window.location.href = url;
            return;
        }
    }

    let json;
    try {
        const ct = res.headers.get("content-type") || "";
        if (ct.includes("application/json")) {
            json = await res.json();
        } else {
            const txt = await res.text();
            showLoginError("Resposta inesperada do servidor: " + txt);
            return;
        }
    } catch (err) {
        showLoginError("Erro ao processar resposta: " + err.message);
        return;
    }

    if (json.status) {
        showLoginSuccess(json.msg || "Login realizado com sucesso");
        setTimeout(() => {
            window.location.href = "/dashboard";
        }, 500);
    } else {
        showLoginError(json.msg || "Usuário ou senha inválidos");
    }
});

// ================= PRÉ-CADASTRO =================

document.getElementById("buttaoPrecadastro")?.addEventListener("click", async () => {
    const nome = document.getElementById("nome").value;
    const email = document.getElementById("email").value;
    const telefone = document.getElementById("telefone").value;
    const whatsapp = document.getElementById("whatsapp").value;
    const senha = document.getElementById("senha").value;

    showPrecadastroAlert("", true);

    let response;
    try {
        response = await fetch("/login/precadastro", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify({
                nome,
                email,
                telefone,
                whatsapp,
                senhaCadastro: senha
            })
        });
    } catch (err) {
        showPrecadastroAlert("Erro de rede: " + err.message, false);
        return;
    }

    if (response.status === 401) {
        showPrecadastroAlert("Não autorizado (401).", false);
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
            return;
        }
    } catch (err) {
        showPrecadastroAlert("Erro ao processar resposta: " + err.message, false);
        return;
    }

    if (data.status) {
        showPrecadastroAlert(data.msg || "Cadastro realizado com sucesso!", true);

        const modalEl = document.getElementById("modalPreCadastro");
        if (modalEl) {
            const modal =
                bootstrap.Modal.getInstance(modalEl) ||
                new bootstrap.Modal(modalEl);

            setTimeout(() => modal.hide(), 800);
        }

        document.getElementById("precadastro")?.reset();
    } else {
        showPrecadastroAlert(data.msg || "Erro no cadastro", false);
    }
});
