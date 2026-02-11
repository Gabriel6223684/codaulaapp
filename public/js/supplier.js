// /js/supplier.js

const formulario = document.getElementById('meuFormulario');

if (formulario) {
    formulario.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        console.log('Enviando dados:', payload);

        const url = payload.acao === 'c' ? '/supplier/insert' : '/supplier/update';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            console.log('Status:', response.status);
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Erro HTTP:', text);
                    throw new Error('HTTP ' + response.status + ': ' + text.substring(0, 200));
                });
            }
            return response.text().then(text => {
                console.log('Resposta bruta:', text);
                
                const jsonMatch = text.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    console.log('JSON encontrado:', jsonMatch[0]);
                    try {
                        return JSON.parse(jsonMatch[0]);
                    } catch(e) {
                        console.error('Erro ao parsear JSON:', e);
                        throw new Error('Resposta inválida do servidor');
                    }
                } else {
                    throw new Error('Nenhum JSON encontrado na resposta');
                }
            });
        })
        .then(data => {
            console.log('Dados parsados:', data);
            if (data.status === true) {
                alert(data.msg);
                window.location.href = '/supplier/lista';
            } else {
                console.error("Erro do Servidor:", data.msg);
                alert("Erro: " + data.msg);
            }
        })
        .catch(error => {
            console.error("Erro na requisição:", error);
            alert("Erro na comunicação:\n" + error.message);
        });
    });
}
