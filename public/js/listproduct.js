// /js/listproduct.js
async function Deletar(id) {
    if (!confirm('Deseja realmente excluir este produto?')) return;

    try {
        const response = await fetch(`/product/deletar/${id}`, {
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

// Abrir modal de ajuste de estoque
async function AbrirModalEstoque(id, nome) {
    document.getElementById('produtoId').value = id;
    document.getElementById('produtoNome').value = nome;
    document.getElementById('estoqueAnterior').value = '';
    document.getElementById('novoEstoque').value = '';
    
    // Buscar estoque atual
    try {
        const response = await fetch(`/product/getstock/${id}`);
        const data = await response.json();
        
        if (data.status) {
            document.getElementById('estoqueAnterior').value = data.data.estoque_atual;
        } else {
            document.getElementById('estoqueAnterior').value = '0';
        }
    } catch (error) {
        console.error('Erro ao buscar estoque:', error);
        document.getElementById('estoqueAnterior').value = '0';
    }
    
    // Abrir o modal
    const modal = new bootstrap.Modal(document.getElementById('modalEstoque'));
    modal.show();
}

window.AbrirModalEstoque = AbrirModalEstoque;

// Salvar novo estoque
async function SalvarEstoque() {
    const id_produto = document.getElementById('produtoId').value;
    const novo_estoque = document.getElementById('novoEstoque').value;
    
    if (!novo_estoque || novo_estoque < 0) {
        alert('Por favor, insira um valor válido para o novo estoque.');
        return;
    }
    
    try {
        const response = await fetch('/product/adjuststock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_produto=${id_produto}&novo_estoque=${novo_estoque}`
        });
        
        const data = await response.json();
        
        if (data.status) {
            alert(data.msg);
            // Fechar o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEstoque'));
            modal.hide();
            // Recarregar a página para atualizar os dados
            location.reload();
        } else {
            alert('Erro: ' + data.msg);
        }
    } catch (error) {
        console.error('Erro ao salvar estoque:', error);
        alert('Erro ao salvar estoque.');
    }
}

window.SalvarEstoque = SalvarEstoque;
