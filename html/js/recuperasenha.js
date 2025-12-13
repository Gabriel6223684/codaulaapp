fetch('/recuperar-senha.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email })
})
.then(res => res.text()) // ler como texto
.then(text => {
    try {
        const data = JSON.parse(text);
        alert(data.message);
        if (data.success) {
            // Passa para etapa 2
        }
    } catch(e) {
        console.error('Erro ao parsear JSON:', text);
        alert('Erro no servidor. Veja console.');
    }
})
.catch(err => console.error(err));


document.addEventListener('DOMContentLoaded', function () {
    let etapa = 1; // 1 = enviar email, 2 = digitar código + nova senha

    const btn = document.getElementById('btnRecuperarSenha');
    const etapaEmailDiv = document.getElementById('etapaEmail');
    const etapaCodigoDiv = document.getElementById('etapaCodigo');

    btn.addEventListener('click', function () {
        if (etapa === 1) {
            const email = document.getElementById('emailRecuperar').value;
            if (!email) return alert('Informe um e-mail válido!');

            fetch('/localhost/recuperar-senha.php', { // ajuste para o caminho correto
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        etapa = 2;
                        etapaEmailDiv.classList.add('d-none');
                        etapaCodigoDiv.classList.remove('d-none');
                    }
                })
                .catch(err => console.error(err));

        } else if (etapa === 2) {
            const codigo = document.getElementById('codigoRecuperar').value;
            const senha = document.getElementById('novaSenha').value;

            if (!codigo || !senha) return alert('Informe o código e a nova senha!');

            fetch('/localhost/validar-codigo.php', { // ajuste para o caminho correto
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ codigo, senha })
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalRecuperarSenha'));
                        modal.hide();
                        etapa = 1;
                        etapaEmailDiv.classList.remove('d-none');
                        etapaCodigoDiv.classList.add('d-none');
                        document.getElementById('emailRecuperar').value = '';
                        document.getElementById('codigoRecuperar').value = '';
                        document.getElementById('novaSenha').value = '';
                    }
                })
                .catch(err => console.error(err));
        }
    });
});
