document.getElementById("formlogin").addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = new FormData(e.target);

    const res = await fetch("/login", {
        method: "POST",
        body: form
    });

    // Tratamento específico para 401
    if (res.status === 401) {
        const txt = await res.text();
        console.error('Resposta 401 do servidor:', txt || res.status);
        alert('Não autorizado (401). Verifique as credenciais ou logs do servidor.');
        return;
    }

    let json;
    try {
        const ct = res.headers.get('content-type') || '';
        if (ct.indexOf('application/json') !== -1) {
            json = await res.json();
        } else {
            const txt = await res.text();
            throw new Error('Resposta inválida do servidor: ' + (txt || `status ${res.status}`));
        }
    } catch (err) {
        alert('Erro ao processar resposta do servidor: ' + err.message);
        return;
    }

    if (json.status) {
        window.location.href = "/dashboard";
    } else {
        alert(json.msg || json.message || 'Erro no login');
    }
});

document.getElementById("buttaoPrecadastro").addEventListener("click", async () => {
    const nome = document.getElementById("nome").value;
    const email = document.getElementById("email").value;
    const telefone = document.getElementById("telefone").value;
    const whatsapp = document.getElementById("whatsapp").value;
    const senha = document.getElementById("senha").value;

    const response = await fetch("/login/precadastro", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            nome,
            email,
            telefone,
            whatsapp,
            senhaCadastro: senha
        })
    });

    // Tratamento específico para 401
    if (response.status === 401) {
        const txt = await response.text();
        console.error('Resposta 401 do servidor (precadastro):', txt || response.status);
        alert('Não autorizado (401). Verifique configurações do servidor e tente novamente.');
        return;
    }

    let data;
    try {
        const ct = response.headers.get('content-type') || '';
        if (ct.indexOf('application/json') !== -1) {
            data = await response.json();
        } else {
            const txt = await response.text();
            throw new Error('Resposta inválida do servidor: ' + (txt || `status ${response.status}`));
        }
    } catch (err) {
        alert('Erro ao processar resposta do servidor: ' + err.message);
        return;
    }

    if (data.status) {
        alert(data.msg || "Cadastro realizado com sucesso!");
        const modalEl = document.getElementById('modalPreCadastro');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    } else {
        alert(data.msg || "Erro no cadastro");
    }
});