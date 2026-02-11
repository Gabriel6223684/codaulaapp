// /js/user.js

const formulario = document.getElementById('meuFormulario');

if (formulario) {
    formulario.addEventListener('submit', function(e) {
        e.preventDefault(); // Impede o redirecionamento padrão

        // Captura os dados do formulário
        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        console.log('Enviando dados:', payload);

        // Faz a requisição para o endpoint PHP
        fetch('/usuario/insert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            console.log('Status:', response.status);
            console.log('Content-Type:', response.headers.get('content-type'));
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Erro HTTP:', text);
                    throw new Error('HTTP ' + response.status + ': ' + text.substring(0, 200));
                });
            }
            return response.text().then(text => {
                console.log('Resposta bruta:', text);
                
                // Tentar extrair JSON válido da resposta (pode ter warnings HTML antes)
                const jsonMatch = text.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    console.log('JSON encontrado:', jsonMatch[0]);
                    try {
                        return JSON.parse(jsonMatch[0]);
                    } catch(e) {
                        console.error('Erro ao parsear JSON:', e);
                        throw new Error('Resposta inválida do servidor: ' + text.substring(0, 200));
                    }
                } else {
                    throw new Error('Nenhum JSON encontrado na resposta: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            console.log('Dados parsados:', data);
            if (data.status === true) {
                alert("Sucesso: " + data.msg);
                window.location.href = '/usuario/lista'; // Redireciona para a listagem
            } else {
                console.error("Erro do Servidor:", data.msg);
                alert("Erro ao salvar: " + data.msg);
            }
        })
        .catch(error => {
            console.error("Erro na requisição:", error);
            console.error("Stack:", error.stack);
            alert("Erro na comunicação:\n" + error.message);
        });
    });
} else {
    console.error('Formulário com id "meuFormulario" não encontrado!');
}