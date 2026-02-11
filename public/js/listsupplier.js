// /js/listsupplier.js
async function Deletar(id) {
    if (!confirm('Deseja realmente excluir este fornecedor?')) return;

    try {
        const response = await fetch(`/supplier/deletar/${id}`, {
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
