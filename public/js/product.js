// /js/product.js

document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('meuFormulario');

    if (!formulario) {
        console.warn('Form #meuFormulario not found');
        return;
    }

    formulario.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        const url = payload.acao === 'c' ? '/product/insert' : '/product/update';

        console.log('Submitting to:', url, payload);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            console.log('Response:', text);
            const jsonMatch = text.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                return JSON.parse(jsonMatch[0]);
            } else {
                throw new Error('Nenhum JSON encontrado');
            }
        })
        .then(data => {
            if (data.status === true) {
                alert('Produto salvo com sucesso!');
                window.location.reload();
            } else {
                alert("Erro: " + data.msg);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Erro: " + error.message);
        });
    });
});
