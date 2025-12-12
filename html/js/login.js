// Código para o formulário de pré-cadastro
document.getElementById('buttaoPrecadastro').addEventListener('click', function () {
    const form = document.getElementById('precadastro');
    const formData = new FormData(form);

    fetch('/login/precadastro', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                alert('Cadastro realizado e você já está logado!');
                // fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalPreCadastro'));
                modal.hide();
                form.reset();
                // redirecionar para página principal (dashboard)
                window.location.href = '/home'; // ou a rota que seu site usa
            } else {
                alert('Erro: ' + data.msg);
            }
        })

        .catch(err => console.error('Erro na requisição:', err));
});



// Código para o formulário de login
document.getElementById('formlogin').addEventListener('submit', function(e) {
    e.preventDefault(); // evita recarregar a página

    const form = e.target;
    const formData = new FormData(form);

    fetch('/login', { // rota do seu backend para login
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            // login bem-sucedido
            alert('Login realizado com sucesso!');
            form.reset();
            window.location.href = '/home'; // ou a rota da dashboard
        } else {
            // login falhou
            alert('Erro: ' + data.msg);
        }
    })
    .catch(err => console.error('Erro na requisição:', err));
});
