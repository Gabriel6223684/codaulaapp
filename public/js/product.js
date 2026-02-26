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

    // ================== AJUSTE DE ESTOQUE ==================
    
    // Carregar estoque atual quando o modal abrir
    const ajustarEstoqueModal = document.getElementById('ajustarEstoqueModal');
    if (ajustarEstoqueModal) {
        ajustarEstoqueModal.addEventListener('shown.bs.modal', function() {
            loadCurrentStock();
        });
    }

    // Atualizar preview do novo estoque quando mudar a quantidade ou tipo
    const stockQuantity = document.getElementById('stockQuantity');
    const stockEntrada = document.getElementById('stockEntrada');
    const stockSaida = document.getElementById('stockSaida');
    
    if (stockQuantity) {
        stockQuantity.addEventListener('input', updateStockPreview);
    }
    if (stockEntrada) {
        stockEntrada.addEventListener('change', updateStockPreview);
    }
    if (stockSaida) {
        stockSaida.addEventListener('change', updateStockPreview);
    }

    function loadCurrentStock() {
        const idInput = document.querySelector('input[name="id"]');
        if (!idInput) return;
        
        const productId = idInput.value;
        
        fetch(`/product/getstock/${productId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                const currentStockInput = document.getElementById('currentStock');
                if (currentStockInput) {
                    currentStockInput.value = data.data.estoque_atual || 0;
                }
                updateStockPreview();
            }
        })
        .catch(error => {
            console.error('Erro ao carregar estoque:', error);
        });
    }

    function updateStockPreview() {
        const currentStock = parseFloat(document.getElementById('currentStock')?.value) || 0;
        const quantity = parseFloat(document.getElementById('stockQuantity')?.value) || 0;
        const isEntrada = document.getElementById('stockEntrada')?.checked;
        
        const newStock = isEntrada ? (currentStock + quantity) : (currentStock - quantity);
        
        const preview = document.getElementById('newStockPreview');
        if (preview) {
            preview.textContent = newStock;
            preview.parentElement.className = newStock < 0 ? 'alert alert-danger' : 'alert alert-secondary';
        }
    }

    // Confirmar ajuste de estoque
    const confirmAdjustBtn = document.getElementById('confirmAdjustStockBtn');
    if (confirmAdjustBtn) {
        confirmAdjustBtn.addEventListener('click', function() {
            const idInput = document.querySelector('input[name="id"]');
            if (!idInput) {
                alert('ID do produto não encontrado');
                return;
            }

            const productId = idInput.value;
            const quantity = parseFloat(document.getElementById('stockQuantity')?.value) || 0;
            const tipo = document.getElementById('stockEntrada')?.checked ? 'entrada' : 'saida';
            const observacao = document.getElementById('stockObservation')?.value || '';

            if (quantity <= 0) {
                alert('A quantidade deve ser maior que zero');
                return;
            }

            // Verificar se há estoque suficiente para saída
            const currentStock = parseFloat(document.getElementById('currentStock')?.value) || 0;
            if (tipo === 'saida' && quantity > currentStock) {
                alert('Estoque insuficiente! Estoque atual: ' + currentStock);
                return;
            }

            const formData = new FormData();
            formData.append('id_produto', productId);
            formData.append('quantidade', quantity);
            formData.append('tipo', tipo);
            formData.append('observacao', observacao);

            fetch('/product/adjuststock', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    alert('Estoque ajustado com sucesso!\nEstoque anterior: ' + data.data.estoque_anterior + '\nNovo estoque: ' + data.data.estoque_atual);
                    
                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('ajustarEstoqueModal'));
                    modal.hide();
                    
                    // Recarregar os dados do estoque
                    loadCurrentStock();
                    
                    // Limpar campos
                    document.getElementById('stockQuantity').value = 1;
                    document.getElementById('stockObservation').value = '';
                    document.getElementById('stockEntrada').checked = true;
                    document.getElementById('stockSaida').checked = false;
                } else {
                    alert('Erro: ' + data.msg);
                }
            })
            .catch(error => {
                console.error('Erro ao ajustar estoque:', error);
                alert('Erro ao ajustar estoque: ' + error.message);
            });
        });
    }
});
