// /js/listsale.js
async function Deletar(id) {
    if (!confirm('Deseja realmente excluir esta venda?')) return;

    try {
        const response = await fetch(`/venda/deletar/${id}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            location.reload();
        } else {
            alert('Erro ao excluir.');
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

window.Deletar = Deletar;
