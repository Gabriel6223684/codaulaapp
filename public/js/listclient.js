// /js/listclient.js
async function Deletar(id) {
    if (!confirm('Deseja realmente excluir este cliente?')) return;

    try {
        const response = await fetch(`/cliente/deletar/${id}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            // Remove a linha da tabela visualmente sem dar refresh
            location.reload(); 
            // Ou você pode buscar o elemento <tr> e dar um .remove()
        } else {
            alert('Erro ao excluir.');
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// Torna a função global para o atributo onclick do HTML encontrar
window.Deletar = Deletar;
