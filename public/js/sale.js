import { Validate } from "./Validate.js";
import { Requests } from "./Requests.js";

// Variáveis globais
let cart = [];
let productsTable = [];
let discount = { type: 'valor', amount: 0 };
let interest = { type: 'valor', amount: 0 };
let paymentMethod = 'pix';
let paymentType = 'avista'; // 'avista' ou 'parcelado'
let selectedPaymentTerm = null;
let paymentTermsData = [];
let selectedProductFromSearch = null;

// Elementos do DOM
const Action = document.getElementById('acao');
const Id = document.getElementById('id');
const insertItemButton = document.getElementById('insertItemButton');

// Função de log para debug
function log(msg, data = null) {
    console.log(`[Sale] ${msg}`, data || '');
}

// Atualizar relógio em tempo real
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const days = ['Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira',
        'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
    const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

    const dayName = days[now.getDay()];
    const day = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();

    const timeElement = document.querySelector('.time');
    const dateElement = document.querySelector('.date');

    if (timeElement) timeElement.textContent = `${hours}:${minutes}:${seconds}`;
    if (dateElement) dateElement.textContent = `${dayName}, ${day} De ${month} De ${year}`;
}

setInterval(updateClock, 1000);
updateClock();

// ================== FUNÇÕES DEVENDA ==================

async function InsertSale() {
    // Validar se há produtos na tabela
    if (productsTable.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Nenhum produto',
            text: 'Adicione produtos primeiro usando a pesquisa (F4).',
            time: 2000,
            progressBar: true,
        });
        return;
    }

    const valid = Validate.SetForm('form').Validate();
    if (!valid) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Por favor, preencha os campos corretamente.',
            time: 2000,
            progressBar: true,
        });
        return;
    }

    try {
        // Criar ou obter ID da venda
        let saleId = Id.value;
        
        if (!saleId || saleId === '') {
            const response = await Requests.SetForm('form').Post('/venda/insert');
            if (!response.status) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: response.msg || 'Ocorreu um erro ao inserir a venda.',
                    time: 3000,
                    progressBar: true,
                });
                return;
            }
            saleId = response.id;
            Id.value = saleId;
            Action.value = 'e';
            window.history.pushState({}, '', `/venda/alterar/${saleId}`);
        }

        // Adicionar cada produto da tabela ao carrinho/venda
        for (const product of productsTable) {
            try {
                const formData = new FormData();
                formData.append('id_venda', saleId);
                formData.append('id_produto', product.id);
                formData.append('quantidade', '1');
                formData.append('preco_unitario', product.preco_venda);

                const response = await fetch('/venda/insertitem', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                const itemDbId = data.itemId || data.id || null;
                
                // Adicionar ao carrinho local
                addToCart(product.codigo, product.nome, parseFloat(product.preco_venda), itemDbId);
            } catch (error) {
                console.error('[Sale] Erro ao inserir item:', error);
            }
        }

        // Carregar termos de pagamento após inserir
        await loadPaymentTerms();
        
        // Atualizar display
        updateCart();
        
        Swal.fire({
            icon: 'success',
            title: 'Produtos adicionados',
            text: `${productsTable.length} produto(s) adicionado(s) à venda!`,
            time: 2000,
            progressBar: true,
        });
        
        return saleId;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Ocorreu um erro ao inserir a venda.',
            time: 3000,
            progressBar: true,
        });
        return null;
    }
}

// ================== TERMOS DE PAGAMENTO ==================

async function loadPaymentTerms() {
    log('Carregando termos de pagamento...');
    try {
        const response = await fetch('/venda/getpaymentterms', {
            method: 'GET'
        });
        
        const data = await response.json();
        log('Termos de pagamento recebidos:', data);
        
        if (data.status && data.data) {
            paymentTermsData = data.data;
            
            // Atualizar select principal
            const select = document.getElementById('paymentTermsSelect');
            if (select) {
                select.innerHTML = '<option value="">Selecione a condição de pagamento...</option>';
                paymentTermsData.forEach(term => {
                    const option = document.createElement('option');
                    option.value = term.id;
                    const parcelas = term.installments && term.installments.length > 0 ? `(${term.installments.length}x)` : '';
                    option.textContent = `${term.titulo} ${parcelas}`;
                    option.dataset.parcelas = term.installments && term.installments.length > 0 ? term.installments[0].parcela : 1;
                    option.dataset.intervalo = term.installments && term.installments.length > 0 ? term.installments[0].intervalor : 30;
                    select.appendChild(option);
                });
            }
            
            // Atualizar select do modal
            const modalSelect = document.getElementById('modalPaymentTermsSelect');
            if (modalSelect) {
                modalSelect.innerHTML = '<option value="">Selecione a condição...</option>';
                paymentTermsData.forEach(term => {
                    const option = document.createElement('option');
                    option.value = term.id;
                    const parcelas = term.installments && term.installments.length > 0 ? `(${term.installments.length}x)` : '';
                    option.textContent = `${term.titulo} ${parcelas}`;
                    option.dataset.parcelas = term.installments && term.installments.length > 0 ? term.installments[0].parcela : 1;
                    option.dataset.intervalo = term.installments && term.installments.length > 0 ? term.installments[0].intervalor : 30;
                    modalSelect.appendChild(option);
                });
            }
            
            log('Termos de pagamento carregados: ' + paymentTermsData.length);
        }
    } catch (error) {
        console.error('[Sale] Erro ao carregar termos de pagamento:', error);
    }
}

function calculateInstallments() {
    const totalText = document.querySelector('.total-amount')?.textContent || 'R$ 0';
    const total = parseFloat(totalText.replace('R$', '').replace(',', '.').trim()) || 0;
    
    const paymentTermsSelect = document.getElementById('paymentTermsSelect');
    if (!paymentTermsSelect) return;
    
    const selectedOption = paymentTermsSelect.options[paymentTermsSelect.selectedIndex];
    
    if (!selectedOption || !selectedOption.value || total <= 0) {
        const installmentInfo = document.getElementById('installmentInfo');
        const installmentsSection = document.getElementById('installmentsSection');
        if (installmentInfo) installmentInfo.style.display = 'none';
        if (installmentsSection) installmentsSection.style.display = 'none';
        return;
    }
    
    const numParcelas = parseInt(selectedOption.dataset.parcelas) || 1;
    const valorParcela = total / numParcelas;
    
    const installmentCount = document.getElementById('installmentCount');
    const installmentValue = document.getElementById('installmentValue');
    const installmentInfo = document.getElementById('installmentInfo');
    
    if (installmentCount) installmentCount.textContent = `${numParcelas}x`;
    if (installmentValue) installmentValue.textContent = `R$ ${valorParcela.toFixed(2).replace('.', ',')}`;
    if (installmentInfo) installmentInfo.style.display = 'flex';
}

async function savePaymentTermsAndCreateInstallments(saleId, paymentTermsSelectId = 'paymentTermsSelect') {
    const paymentTermsSelect = document.getElementById(paymentTermsSelectId);
    const id_pagamento = paymentTermsSelect?.value;
    
    if (!id_pagamento) {
        return { status: false, msg: 'Selecione uma condição de pagamento' };
    }
    
    const totalText = document.querySelector('.total-amount')?.textContent || 'R$ 0';
    const valor_total = parseFloat(totalText.replace('R$', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
    
    if (valor_total <= 0) {
        return { status: false, msg: 'Valor total da venda deve ser maior que zero' };
    }
    
    const selectedOption = paymentTermsSelect.options[paymentTermsSelect.selectedIndex];
    const num_parcelas = parseInt(selectedOption?.dataset.parcelas) || 1;
    const intervalo = parseInt(selectedOption?.dataset.intervalo) || 30;
    
    try {
        const formDataPayment = new FormData();
        formDataPayment.append('id_venda', saleId);
        formDataPayment.append('id_pagamento', id_pagamento);
        
        const paymentResponse = await fetch('/venda/savepaymentterms', {
            method: 'POST',
            body: formDataPayment
        });
        
        const paymentData = await paymentResponse.json();
        
        if (!paymentData.status) {
            return paymentData;
        }
        
        const formDataInstallments = new FormData();
        formDataInstallments.append('id_venda', saleId);
        formDataInstallments.append('valor_total', valor_total);
        formDataInstallments.append('num_parcelas', num_parcelas);
        formDataInstallments.append('intervalo', intervalo);
        formDataInstallments.append('data_vencimento', new Date().toISOString().split('T')[0]);
        
        const installmentsResponse = await fetch('/venda/createinstallments', {
            method: 'POST',
            body: formDataInstallments
        });
        
        const installmentsData = await installmentsResponse.json();
        
        if (installmentsData.status) {
            displayInstallments(installmentsData.data);
            displayModalInstallments(installmentsData.data);
        }
        
        return installmentsData;
    } catch (error) {
        console.error('[Sale] Erro ao salvar pagamento e parcelas:', error);
        return { status: false, msg: error.message };
    }
}

function displayInstallments(installments) {
    const tbody = document.getElementById('installmentsBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    const installmentsSection = document.getElementById('installmentsSection');
    if (!installments || installments.length === 0) {
        if (installmentsSection) installmentsSection.style.display = 'none';
        return;
    }
    
    installments.forEach(inst => {
        const row = document.createElement('tr');
        const vencimento = new Date(inst.vencimento).toLocaleDateString('pt-BR');
        const valor = parseFloat(inst.valor).toFixed(2).replace('.', ',');
        
        row.innerHTML = `
            <td>${inst.numero}</td>
            <td>R$ ${valor}</td>
            <td>${vencimento}</td>
            <td><span class="badge bg-warning">Pendente</span></td>
        `;
        tbody.appendChild(row);
    });
    
    if (installmentsSection) installmentsSection.style.display = 'block';
}

function displayModalInstallments(installments) {
    const tbody = document.getElementById('modalInstallmentsBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    const installmentsSection = document.getElementById('modalInstallmentsSection');
    if (!installments || installments.length === 0) {
        if (installmentsSection) installmentsSection.style.display = 'none';
        return;
    }
    
    installments.forEach(inst => {
        const row = document.createElement('tr');
        const vencimento = new Date(inst.vencimento).toLocaleDateString('pt-BR');
        const valor = parseFloat(inst.valor).toFixed(2).replace('.', ',');
        
        row.innerHTML = `
            <td>${inst.numero}</td>
            <td>R$ ${valor}</td>
            <td>${vencimento}</td>
            <td><span class="badge bg-warning">Pendente</span></td>
        `;
        tbody.appendChild(row);
    });
    
    if (installmentsSection) installmentsSection.style.display = 'block';
}

// ================== CARRINHO ==================

function saveCartToLocalStorage() {
    try {
        localStorage.setItem('saleCart', JSON.stringify(cart));
        localStorage.setItem('saleCartProductsTable', JSON.stringify(productsTable));
        localStorage.setItem('saleCartDiscount', JSON.stringify(discount));
        localStorage.setItem('saleCartInterest', JSON.stringify(interest));
        log('Dados salvos no localStorage - ProductsTable:', productsTable.length, 'items');
    } catch (e) {
        console.error('[Sale] Erro ao salvar no localStorage:', e);
    }
}

function loadCartFromLocalStorage() {
    try {
        const savedCart = localStorage.getItem('saleCart');
        const savedProductsTable = localStorage.getItem('saleCartProductsTable');
        const savedDiscount = localStorage.getItem('saleCartDiscount');
        const savedInterest = localStorage.getItem('saleCartInterest');
        
        if (savedCart) {
            cart = JSON.parse(savedCart);
            log('Carrinho carregado:', cart.length, 'itens');
        }
        
        if (savedProductsTable) {
            productsTable = JSON.parse(savedProductsTable);
            log('Tabela de produtos carregada:', productsTable.length, 'itens');
        }
        
        if (savedDiscount) {
            discount = JSON.parse(savedDiscount);
            const discountInput = document.getElementById('discountValue');
            if (discountInput && discount.amount) {
                discountInput.value = discount.amount;
            }
        }
        
        if (savedInterest) {
            interest = JSON.parse(savedInterest);
            const interestInput = document.getElementById('interestValue');
            if (interestInput && interest.amount) {
                interestInput.value = interest.amount;
            }
        }
    } catch (e) {
        console.error('[Sale carregar do localStorage] Erro ao:', e);
    }
}

function clearCartFromLocalStorage() {
    localStorage.removeItem('saleCart');
    localStorage.removeItem('saleCartProductsTable');
    localStorage.removeItem('saleCartDiscount');
    localStorage.removeItem('saleCartInterest');
}

function addToCart(code, description, price, dbId = null) {
    const existingItem = cart.find(item => item.code === code);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ code, description, price, quantity: 1, dbId });
    }
    saveCartToLocalStorage();
    updateCart();
}

const updateCart = () => {
    const cartTableBody = document.querySelector('.cart-table tbody');
    if (cartTableBody) {
        cartTableBody.innerHTML = '';
        cart.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.code}</td>
                <td>${item.description}</td>
                <td>R$ ${item.price.toFixed(2).replace('.', ',')}</td>
                <td>${item.quantity}</td>
                <td>R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</td>
                <td>
                    <button class="btn btn-danger btn-sm btn-remove-item" data-index="${index}" title="Remover">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            cartTableBody.appendChild(row);
        });
        
        document.querySelectorAll('.btn-remove-item').forEach(button => {
            button.addEventListener('click', async function() {
                const index = parseInt(this.dataset.index);
                await removeFromCart(index);
            });
        });
        
        const cartItemCount = document.getElementById('cartItemCount');
        if (cartItemCount) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartItemCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
        }
    }
    updateTotals();
};

async function removeFromCart(index) {
    const item = cart[index];
    let saleId = Id.value;
    
    if (saleId && saleId !== '' && item.dbId) {
        try {
            const response = await fetch(`/venda/deleteitem/${item.dbId}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            
            if (!data.status) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.msg || 'Erro ao remover item do banco de dados'
                });
                return;
            }
        } catch (error) {
            console.error('[Sale] Erro ao remover item:', error);
        }
    }
    
    cart.splice(index, 1);
    saveCartToLocalStorage();
    updateCart();
    
    Swal.fire({
        icon: 'success',
        title: 'Item removido',
        text: `${item.description} removido do carrinho`,
        timer: 1500,
        showConfirmButton: false
    });
}

// ================== TOTAIS ==================

const updateTotals = () => {
    const totalAmount = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    let discountAmount = 0;
    let interestAmount = 0;
    
    const discountInput = document.getElementById('discountValue');
    if (discountInput && discountInput.value) {
        discountAmount = parseFloat(discountInput.value.replace(',', '.')) || 0;
    }
    
    const interestInput = document.getElementById('interestValue');
    if (interestInput && interestInput.value) {
        interestAmount = parseFloat(interestInput.value.replace(',', '.')) || 0;
    }
    
    const finalAmount = totalAmount - discountAmount + interestAmount;
    
    const totalAmountElement = document.querySelector('.total-amount');
    if (totalAmountElement) {
        totalAmountElement.textContent = `R$ ${finalAmount.toFixed(2)}`;
    }
    
    const subtotalElement = document.querySelector('.subtotal .amount');
    if (subtotalElement) {
        subtotalElement.textContent = `R$ ${totalAmount.toFixed(2)}`;
    }
    
    calculateInstallments();
};

// ================== PRODUTOS ==================

function updateProductCount() {
    const tbody = document.getElementById('productsTableBody');
    const countElement = document.getElementById('total_item');
    if (tbody && countElement) {
        const rowCount = tbody.querySelectorAll('.product-row').length;
        countElement.textContent = rowCount;
    }
}

function addProductToTable(product) {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) {
        log('productsTableBody não encontrado!');
        return;
    }
    
    // Verificar se o produto já existe na tabela
    const existingProduct = productsTable.find(p => p.id === product.id);
    if (!existingProduct) {
        productsTable.push(product);
        log('Produto adicionado ao productsTable:', product);
    }
    
    // Verificar se já existe na UI
    const existingRow = tbody.querySelector(`tr[data-id="${product.id}"]`);
    if (existingRow) {
        log('Produto já existe na tabela UI');
        return;
    }
    
    const row = document.createElement('tr');
    row.className = 'product-row';
    row.dataset.id = product.id;
    row.dataset.codigo = product.codigo;
    row.dataset.nome = product.nome;
    row.dataset.preco = product.preco_venda;
    
    row.innerHTML = `
        <td>${product.codigo || ''}</td>
        <td>${product.nome || ''}</td>
        <td>R$ ${parseFloat(product.preco_venda || 0).toFixed(2).replace('.', ',')}</td>
        <td>1</td>
        <td>
            <button class="btn btn-success btn-sm btn-add-to-cart" title="Adicionar ao Carrinho">
                <i class="fas fa-cart-plus"></i>
            </button>
            <button class="btn btn-danger btn-sm btn-remove-from-table" title="Remover">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    updateProductCount();
    saveCartToLocalStorage();
    
    // Botão adicionar ao carrinho
    row.querySelector('.btn-add-to-cart').addEventListener('click', function() {
        addToCart(product.codigo, product.nome, parseFloat(product.preco_venda));
        Swal.fire({
            icon: 'success',
            title: 'Adicionado',
            text: `${product.nome} adicionado ao carrinho!`,
            timer: 1500,
            showConfirmButton: false
        });
    });
    
    // Botão remover
    row.querySelector('.btn-remove-from-table').addEventListener('click', function() {
        row.remove();
        updateProductCount();
        productsTable = productsTable.filter(p => p.id !== row.dataset.id);
        saveCartToLocalStorage();
    });
    
    log('Produto adicionado à tabela:', product);
}

function restoreProductsTable() {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) {
        log('productsTableBody não encontrado para restauração!');
        return;
    }
    
    tbody.innerHTML = '';
    
    log('Restaurando produtos da tabela:', productsTable.length);
    
    productsTable.forEach(product => {
        const row = document.createElement('tr');
        row.className = 'product-row';
        row.dataset.id = product.id;
        row.dataset.codigo = product.codigo;
        row.dataset.nome = product.nome;
        row.dataset.preco = product.preco_venda;
        
        row.innerHTML = `
            <td>${product.codigo || ''}</td>
            <td>${product.nome || ''}</td>
            <td>R$ ${parseFloat(product.preco_venda || 0).toFixed(2).replace('.', ',')}</td>
            <td>1</td>
            <td>
                <button class="btn btn-success btn-sm btn-add-to-cart" title="Adicionar ao Carrinho">
                    <i class="fas fa-cart-plus"></i>
                </button>
                <button class="btn btn-danger btn-sm btn-remove-from-table" title="Remover">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        
        row.querySelector('.btn-add-to-cart').addEventListener('click', function() {
            addToCart(product.codigo, product.nome, parseFloat(product.preco_venda));
            Swal.fire({
                icon: 'success',
                title: 'Adicionado',
                text: `${product.nome} adicionado ao carrinho!`,
                timer: 1500,
                showConfirmButton: false
            });
        });
        
        row.querySelector('.btn-remove-from-table').addEventListener('click', function() {
            row.remove();
            updateProductCount();
            productsTable = productsTable.filter(p => p.id !== row.dataset.id);
            saveCartToLocalStorage();
        });
    });
    
    updateProductCount();
    log('Tabela de produtos restaurada com', productsTable.length, 'itens');
}

async function loadSaleData() {
    const saleId = Id.value;
    log('loadSaleData chamado - saleId:', saleId);
    
    if (!saleId) {
        log('Nenhum saleId encontrado');
        return;
    }
    
    try {
        log('Buscando dados da venda...');
        const response = await Requests.Get(`/venda/get/${saleId}`);
        log('Resposta da API:', response);
        
        if (response.status && response.data) {
            if (response.data.sale) {
                const sale = response.data.sale;
                const discountValue = document.getElementById('discountValue');
                const interestValue = document.getElementById('interestValue');
                const paymentTermsSelect = document.getElementById('paymentTermsSelect');
                
                if (discountValue && sale.desconto) {
                    discountValue.value = sale.desconto;
                    discount.amount = parseFloat(sale.desconto) || 0;
                }
                if (interestValue && sale.acrescimo) {
                    interestValue.value = sale.acrescimo;
                    interest.amount = parseFloat(sale.acrescimo) || 0;
                }
                if (paymentTermsSelect && sale.id_pagamento) {
                    paymentTermsSelect.value = sale.id_pagamento;
                    calculateInstallments();
                }
            }
            
            if (response.data.items && response.data.items.length > 0) {
                log('Itens encontrados:', response.data.items.length);
                
                response.data.items.forEach(item => {
                    const produtoCodigo = item.produto_codigo || '';
                    const produtoNome = item.produto_nome || '';
                    const precoUnitario = parseFloat(item.preco_unitario) || 0;
                    const quantidade = parseFloat(item.quantidade) || 1;
                    
                    cart.push({
                        code: produtoCodigo,
                        description: produtoNome,
                        price: precoUnitario,
                        quantity: quantidade,
                        dbId: item.id || null
                    });
                });
                
                updateCart();
                log('Carrinho atualizado com sucesso');
            }
            
            const installmentsResponse = await Requests.Get(`/venda/getinstallments/${saleId}`);
            if (installmentsResponse.status && installmentsResponse.data) {
                displayInstallments(installmentsResponse.data);
            }
            
            await loadPaymentTerms();
        }
    } catch (error) {
        console.error('[Sale] Erro ao carregar dados da venda:', error);
    }
}

async function loadProductsInModal(search = '') {
    try {
        const formData = new FormData();
        formData.append('search', search);
        
        const response = await fetch('/produto/listproductdata', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status && data.results) {
            const tbody = document.getElementById('tabelaProdutosBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            data.results.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.codigo || ''}</td>
                    <td>${product.nome || ''}</td>
                    <td>${product.codigo_barra || ''}</td>
                    <td>R$ ${parseFloat(product.preco_venda || 0).toFixed(2).replace('.', ',')}</td>
                    <td>
                        <button class="btn btn-success btn-sm btn-select-product" 
                                data-id="${product.id}" 
                                data-nome="${product.nome}" 
                                data-codigo="${product.codigo}" 
                                data-preco="${product.preco_venda}">
                            <i class="fas fa-plus"></i> Selecionar
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            document.querySelectorAll('.btn-select-product').forEach(button => {
                button.addEventListener('click', function() {
                    const id_produto = this.dataset.id;
                    const nome = this.dataset.nome;
                    const codigo = this.dataset.codigo;
                    const preco = parseFloat(this.dataset.preco);
                    
                    addProductToTable({id: id_produto, nome: nome, codigo: codigo, preco_venda: preco});
                    
                    const modalEl = document.getElementById('pesquisaProdutoModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                });
            });
        }
    } catch (error) {
        console.error('[Sale] Erro ao carregar produtos:', error);
    }
}

const filterProducts = (searchTerm) => {
    const productRows = document.querySelectorAll('.product-row');
    productRows.forEach(row => {
        const code = row.cells[0]?.textContent.toLowerCase() || '';
        const description = row.cells[1]?.textContent.toLowerCase() || '';
        const price = row.cells[2]?.textContent.toLowerCase() || '';
        
        if (code.includes(searchTerm) || description.includes(searchTerm) || price.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
};

// ================== MODAL FINALIZAR VENDA ==================

function openFinalizarVendaModal() {
    const totalAmount = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    
    let discountAmount = 0;
    let interestAmount = 0;
    
    const discountInput = document.getElementById('discountValue');
    if (discountInput && discountInput.value) {
        discountAmount = parseFloat(discountInput.value.replace(',', '.')) || 0;
    }
    
    const interestInput = document.getElementById('interestValue');
    if (interestInput && interestInput.value) {
        interestAmount = parseFloat(interestInput.value.replace(',', '.')) || 0;
    }
    
    const finalAmount = totalAmount - discountAmount + interestAmount;
    
    const modalSubtotal = document.getElementById('modalSubtotal');
    const modalDesconto = document.getElementById('modalDesconto');
    const modalTotal = document.getElementById('modalTotal');
    const modalDiscountValue = document.getElementById('modalDiscountValue');
    const modalInterestValue = document.getElementById('modalInterestValue');
    
    if (modalSubtotal) modalSubtotal.textContent = `R$ ${totalAmount.toFixed(2).replace('.', ',')}`;
    if (modalDesconto) modalDesconto.textContent = `R$ ${discountAmount.toFixed(2).replace('.', ',')}`;
    if (modalTotal) modalTotal.textContent = `R$ ${finalAmount.toFixed(2).replace('.', ',')}`;
    if (modalDiscountValue) modalDiscountValue.value = discountInput?.value || '';
    if (modalInterestValue) modalInterestValue.value = interestInput?.value || '';
    
    paymentType = 'avista';
    
    loadPaymentTerms();
    
    const modalPaymentTypeButtons = document.getElementById('modalPaymentTypeButtons');
    if (modalPaymentTypeButtons) {
        modalPaymentTypeButtons.querySelectorAll('.payment-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.type === 'avista') {
                btn.classList.add('active');
            }
        });
    }
    
    const modalPaymentButtons = document.getElementById('modalPaymentButtons');
    if (modalPaymentButtons) {
        modalPaymentButtons.querySelectorAll('.payment-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.method === 'pix') {
                btn.classList.add('active');
            }
        });
    }
    
    // Reset payment details visibility
    const pixPaymentDetails = document.getElementById('pixPaymentDetails');
    const cardPaymentDetails = document.getElementById('cardPaymentDetails');
    if (pixPaymentDetails) pixPaymentDetails.style.display = 'block';
    if (cardPaymentDetails) cardPaymentDetails.style.display = 'none';
    
    // Resetar select de parcelas
    const installmentCountSelect = document.getElementById('modalInstallmentCountSelect');
    if (installmentCountSelect) {
        installmentCountSelect.value = '';
    }
    
    const avistaSection = document.getElementById('avistaPaymentSection');
    const parceladoSection = document.getElementById('parceladoSection');
    const modalInstallmentInfo = document.getElementById('modalInstallmentInfo');
    const modalInstallmentsSection = document.getElementById('modalInstallmentsSection');
    
    if (avistaSection) avistaSection.style.display = 'block';
    if (parceladoSection) parceladoSection.style.display = 'none';
    if (modalInstallmentInfo) modalInstallmentInfo.style.display = 'none';
    if (modalInstallmentsSection) modalInstallmentsSection.style.display = 'none';
    
    const modalEl = document.getElementById('finalizarVendaModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function switchPaymentType(type) {
    paymentType = type;
    
    const avistaSection = document.getElementById('avistaPaymentSection');
    const parceladoSection = document.getElementById('parceladoSection');
    
    if (type === 'avista') {
        if (avistaSection) avistaSection.style.display = 'block';
        if (parceladoSection) parceladoSection.style.display = 'none';
    } else {
        if (avistaSection) avistaSection.style.display = 'none';
        if (parceladoSection) parceladoSection.style.display = 'block';
    }
    
    log('Tipo de pagamento alterado para:', type);
}

function calculateModalInstallments() {
    const totalText = document.getElementById('modalTotal')?.textContent || 'R$ 0';
    const total = parseFloat(totalText.replace('R$', '').replace(',', '.').trim()) || 0;
    
    // Pegar quantidade de parcelas do select
    const installmentCountSelect = document.getElementById('modalInstallmentCountSelect');
    if (!installmentCountSelect || !installmentCountSelect.value || total <= 0) {
        const installmentInfo = document.getElementById('modalInstallmentInfo');
        const installmentsSection = document.getElementById('modalInstallmentsSection');
        if (installmentInfo) installmentInfo.style.display = 'none';
        if (installmentsSection) installmentsSection.style.display = 'none';
        return;
    }
    
    const numParcelas = parseInt(installmentCountSelect.value) || 1;
    const valorParcela = total / numParcelas;
    
    const installmentCount = document.getElementById('modalInstallmentCount');
    const installmentValue = document.getElementById('modalInstallmentValue');
    const installmentInfo = document.getElementById('modalInstallmentInfo');
    
    if (installmentCount) installmentCount.textContent = `${numParcelas}x`;
    if (installmentValue) installmentValue.textContent = `R$ ${valorParcela.toFixed(2).replace('.', ',')}`;
    if (installmentInfo) installmentInfo.style.display = 'flex';
    
    // Atualizar parcelas na tabela
    const installments = [];
    for (let i = 1; i <= numParcelas; i++) {
        installments.push({
            numero: i,
            valor: valorParcela,
            vencimento: new Date(Date.now() + (i * 30 * 24 * 60 * 60 * 1000)).toISOString().split('T')[0]
        });
    }
    displayModalInstallments(installments);
}

async function confirmFinalizeSale() {
    if (cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Carrinho vazio',
            text: 'Adicione produtos primeiro.',
            time: 2000,
            progressBar: true,
        });
        return;
    }

    if (!paymentType) {
        Swal.fire({
            icon: 'warning',
            title: 'Tipo de pagamento obrigatório',
            text: 'Por favor, selecione se o pagamento será À Vista ou Parcelado.',
            time: 2000,
            progressBar: true,
        });
        return;
    }

    const total = document.querySelector('.total-amount')?.textContent || 'R$ 0';
    
    let saleId = Id.value;
    
    if (!saleId || saleId === '') {
        const saleResponse = await fetch('/venda/insert', {
            method: 'POST',
            body: new FormData(document.getElementById('form'))
        });
        const saleData = await saleResponse.json();
        
        if (!saleData.status) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: saleData.msg || 'Erro ao criar venda'
            });
            return;
        }
        
        saleId = saleData.id;
        Id.value = saleId;
        Action.value = 'e';
        window.history.pushState({}, '', `/venda/alterar/${saleId}`);
    }
    
    const modalDiscountValue = document.getElementById('modalDiscountValue')?.value || 0;
    const modalInterestValue = document.getElementById('modalInterestValue')?.value || 0;
    
    // Se parcelado, validar seleção de parcelas
    if (paymentType === 'parcelado') {
        const installmentCountSelect = document.getElementById('modalInstallmentCountSelect');
        if (!installmentCountSelect || !installmentCountSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Parcelas obrigatória',
                text: 'Por favor, selecione a quantidade de parcelas.',
                time: 2000,
                progressBar: true,
            });
            return;
        }
    }
    
    let metodoPagamento = '';
    if (paymentType === 'avista') {
        const activePaymentBtn = document.querySelector('#modalPaymentButtons .payment-btn.active');
        if (activePaymentBtn) {
            metodoPagamento = activePaymentBtn.dataset.method;
        }
    }
    
    // Salvar desconto e juros
    try {
        const formDataUpdate = new FormData();
        formDataUpdate.append('id', saleId);
        formDataUpdate.append('desconto', modalDiscountValue.replace(',', '.'));
        formDataUpdate.append('acrescimo', modalInterestValue.replace(',', '.'));
        
        await fetch('/venda/update', {
            method: 'POST',
            body: formDataUpdate
        });
        
        const mainDiscountInput = document.getElementById('discountValue');
        const mainInterestInput = document.getElementById('interestValue');
        if (mainDiscountInput) mainDiscountInput.value = modalDiscountValue;
        if (mainInterestInput) mainInterestInput.value = modalInterestValue;
        discount.amount = parseFloat(modalDiscountValue.replace(',', '.')) || 0;
        interest.amount = parseFloat(modalInterestValue.replace(',', '.')) || 0;
        
    } catch (error) {
        console.error('[Sale] Erro ao atualizar venda:', error);
    }
    
    // Processar pagamento
    let paymentResult;
    if (paymentType === 'parcelado') {
        const installmentCountSelect = document.getElementById('modalInstallmentCountSelect');
        const numParcelas = parseInt(installmentCountSelect?.value) || 1;
        
        // Criar parcelas manualmente
        const totalText = document.querySelector('.total-amount')?.textContent || 'R$ 0';
        const valor_total = parseFloat(totalText.replace('R$', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
        const valorParcela = valor_total / numParcelas;
        
        try {
            const formDataPayment = new FormData();
            formDataPayment.append('id_venda', saleId);
            formDataPayment.append('id_pagamento', '1'); // Condição padrão
            
            await fetch('/venda/savepaymentterms', {
                method: 'POST',
                body: formDataPayment
            });
            
            // Criar parcelas
            const installmentsData = [];
            for (let i = 1; i <= numParcelas; i++) {
                installmentsData.push({
                    numero: i,
                    valor: valorParcela,
                    vencimento: new Date(Date.now() + (i * 30 * 24 * 60 * 60 * 1000)).toISOString().split('T')[0]
                });
            }
            
            // Salvar cada parcela
            for (const inst of installmentsData) {
                const formDataInst = new FormData();
                formDataInst.append('id_venda', saleId);
                formDataInst.append('numero', inst.numero);
                formDataInst.append('valor', inst.valor);
                formDataInst.append('vencimento', inst.vencimento);
                
                await fetch('/venda/createinstallments', {
                    method: 'POST',
                    body: formDataInst
                });
            }
            
            displayModalInstallments(installmentsData);
            paymentResult = { status: true };
            
        } catch (error) {
            console.error('[Sale] Erro ao criar parcelas:', error);
            paymentResult = { status: false, msg: error.message };
        }
        
        if (!paymentResult.status) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: paymentResult.msg || 'Erro ao processar pagamento',
                time: 3000,
                progressBar: true,
            });
            return;
        }
    } else {
        // À vista
        try {
            const formDataPayment = new FormData();
            formDataPayment.append('id_venda', saleId);
            formDataPayment.append('id_pagamento', '');
            formDataPayment.append('tipo', 'avista');
            formDataPayment.append('metodo', metodoPagamento);
            
            const paymentResponse = await fetch('/venda/savepaymentterms', {
                method: 'POST',
                body: formDataPayment
            });
            
            paymentResult = await paymentResponse.json();
            
            if (!paymentResult.status) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: paymentResult.msg || 'Erro ao processar pagamento',
                    time: 3000,
                    progressBar: true,
                });
                return;
            }
        } catch (error) {
            console.error('[Sale] Erro ao salvar pagamento à vista:', error);
            paymentResult = { status: true };
        }
    }

    const modalEl = document.getElementById('finalizarVendaModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    Swal.fire({
        icon: 'success',
        title: 'Venda Finalizada',
        text: `Venda no valor de ${total} finalizada com sucesso!`,
        time: 3000,
        progressBar: true,
    }).then(() => {
        cart = [];
        productsTable = [];
        discount = { type: 'valor', amount: 0 };
        interest = { type: 'valor', amount: 0 };
        clearCartFromLocalStorage();
        
        const discountEl = document.getElementById('discountValue');
        const interestEl = document.getElementById('interestValue');
        const paymentTermsEl = document.getElementById('paymentTermsSelect');
        const installmentInfo = document.getElementById('installmentInfo');
        const installmentsSection = document.getElementById('installmentsSection');
        
        if (discountEl) discountEl.value = '';
        if (interestEl) interestEl.value = '';
        if (paymentTermsEl) paymentTermsEl.value = '';
        if (installmentInfo) installmentInfo.style.display = 'none';
        if (installmentsSection) installmentsSection.style.display = 'none';
        
        updateCart();
        
        window.location.href = '/venda/lista';
    });
}

// ================== EVENT LISTENERS ==================

document.addEventListener('DOMContentLoaded', function () {
    log('DOMContentLoaded - ID value:', Id.value);
    log('localStorage disponível:', typeof localStorage !== 'undefined');
    
    loadPaymentTerms();
    
    if (Id.value && Id.value !== '') {
        log('Carregando dados da venda do banco...');
        setTimeout(() => {
            loadSaleData();
        }, 100);
    } else {
        log('Carregando do localStorage...');
        loadCartFromLocalStorage();
        
        if (cart.length > 0) {
            updateCart();
            log('Carrinho restaurado:', cart.length, 'itens');
        }
        
        if (productsTable.length > 0) {
            log('Restaurando tabela de produtos...');
            restoreProductsTable();
        } else {
            log('Nenhum produto na tabela para restaurar');
        }
    }
    
    const paymentTermsSelect = document.getElementById('paymentTermsSelect');
    if (paymentTermsSelect) {
        paymentTermsSelect.addEventListener('change', function() {
            calculateInstallments();
        });
    }
    
    const modalPaymentTermsSelect = document.getElementById('modalPaymentTermsSelect');
    if (modalPaymentTermsSelect) {
        modalPaymentTermsSelect.addEventListener('change', function() {
            calculateModalInstallments();
        });
    }
    
    // Listener para seleção de quantidade de parcelas
    const installmentCountSelect = document.getElementById('modalInstallmentCountSelect');
    if (installmentCountSelect) {
        installmentCountSelect.addEventListener('change', function() {
            calculateModalInstallments();
        });
    }
    
    // Listener para seleção de tipo de pagamento (À Vista / Parcelado)
    const modalPaymentTypeButtons = document.getElementById('modalPaymentTypeButtons');
    if (modalPaymentTypeButtons) {
        modalPaymentTypeButtons.querySelectorAll('.payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                modalPaymentTypeButtons.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                switchPaymentType(this.dataset.type);
            });
        });
    }
    
    const discountInput = document.getElementById('discountValue');
    if (discountInput) {
        discountInput.addEventListener('input', function() {
            discount.amount = parseFloat(this.value.replace(',', '.')) || 0;
            saveCartToLocalStorage();
            updateTotals();
        });
    }
    
    const interestInput = document.getElementById('interestValue');
    if (interestInput) {
        interestInput.addEventListener('input', function() {
            interest.amount = parseFloat(this.value.replace(',', '.')) || 0;
            saveCartToLocalStorage();
            updateTotals();
        });
    }
    
    const modalDiscountInput = document.getElementById('modalDiscountValue');
    if (modalDiscountInput) {
        modalDiscountInput.addEventListener('input', function() {
            const totalAmount = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            const discountAmount = parseFloat(this.value.replace(',', '.')) || 0;
            const interestAmount = parseFloat(document.getElementById('modalInterestValue')?.value.replace(',', '.') || 0);
            const finalAmount = totalAmount - discountAmount + interestAmount;
            
            const modalTotal = document.getElementById('modalTotal');
            const modalDesconto = document.getElementById('modalDesconto');
            if (modalTotal) modalTotal.textContent = `R$ ${finalAmount.toFixed(2).replace('.', ',')}`;
            if (modalDesconto) modalDesconto.textContent = `R$ ${discountAmount.toFixed(2).replace('.', ',')}`;
            
            calculateModalInstallments();
        });
    }
    
    const modalInterestInput = document.getElementById('modalInterestValue');
    if (modalInterestInput) {
        modalInterestInput.addEventListener('input', function() {
            const totalAmount = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            const discountAmount = parseFloat(document.getElementById('modalDiscountValue')?.value.replace(',', '.') || 0);
            const interestAmount = parseFloat(this.value.replace(',', '.')) || 0;
            const finalAmount = totalAmount - discountAmount + interestAmount;
            
            const modalTotal = document.getElementById('modalTotal');
            if (modalTotal) modalTotal.textContent = `R$ ${finalAmount.toFixed(2).replace('.', ',')}`;
            
            calculateModalInstallments();
        });
    }
    
    const productsTableBody = document.getElementById('productsTableBody');
    if (productsTableBody) {
        productsTableBody.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btn-remove-from-table');
            
            if (btnRemove) {
                const row = btnRemove.closest('tr');
                row.remove();
                updateProductCount();
                productsTable = productsTable.filter(p => p.id !== row.dataset.id);
                saveCartToLocalStorage();
            }
        });
    }
    
    const paymentButtons = document.querySelectorAll('#modalPaymentButtons .payment-btn');
    paymentButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            paymentButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            paymentMethod = this.dataset.method;
            
            // Toggle visibility of payment details based on method
            const pixDetails = document.getElementById('pixPaymentDetails');
            const cardDetails = document.getElementById('cardPaymentDetails');
            
            if (paymentMethod === 'pix') {
                if (pixDetails) pixDetails.style.display = 'block';
                if (cardDetails) cardDetails.style.display = 'none';
            } else if (paymentMethod === 'cartao') {
                if (pixDetails) pixDetails.style.display = 'none';
                if (cardDetails) cardDetails.style.display = 'block';
            } else {
                // Dinheiro - hide both
                if (pixDetails) pixDetails.style.display = 'none';
                if (cardDetails) cardDetails.style.display = 'none';
            }
            
            log('Método de pagamento selecionado:', paymentMethod);
        });
    });

    const searchButton = document.querySelector('.btn-search');
    const searchInput = document.querySelector('.search-input');

    if (searchButton) {
        searchButton.addEventListener('click', function () {
            const term = searchInput?.value.toLowerCase() || '';
            filterProducts(term);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                filterProducts(this.value.toLowerCase());
            }
        });
    }

    const finalizeButton = document.getElementById('btnFinalizarVenda');
    if (finalizeButton) {
        finalizeButton.addEventListener('click', function () {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Carrinho vazio',
                    text: 'Adicione produtos primeiro.',
                    time: 2000,
                    progressBar: true,
                });
                return;
            }
            openFinalizarVendaModal();
        });
    }

    const confirmFinalizeButton = document.getElementById('confirmFinalizeButton');
    if (confirmFinalizeButton) {
        confirmFinalizeButton.addEventListener('click', async function () {
            await confirmFinalizeSale();
        });
    }

    const cancelButton = document.querySelector('.btn-cancel');
    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            if (cart.length === 0 && productsTable.length === 0) return;

            const confirmation = confirm('Deseja cancelar a venda atual?');

            if (confirmation) {
                cart = [];
                productsTable = [];
                discount = { type: 'valor', amount: 0 };
                interest = { type: 'valor', amount: 0 };
                clearCartFromLocalStorage();
                
                const discountEl = document.querySelector('.discount-value');
                if (discountEl) discountEl.value = '0';
                
                updateCart();
                alert('Venda cancelada!');
            }
        });
    }
});

document.getElementById('pesquisaProdutoModal')?.addEventListener('shown.bs.modal', function () {
    loadProductsInModal();
});

document.getElementById('pesquisaModal')?.addEventListener('input', function(e) {
    loadProductsInModal(e.target.value);
});

$('#pesquisa').select2({
    theme: 'bootstrap-5',
    placeholder: "Selecione um produto",
    language: "pt-BR",
    ajax: {
        url: '/produto/listproductdata',
        type: 'POST'
    }
});

$('.form-select').on('select2:open', function (e) {
    const inputElement = document.querySelector('.select2-search__field');
    if (inputElement) {
        inputElement.placeholder = 'Digite para pesquisar...';
        inputElement.focus();
    }
});

insertItemButton?.addEventListener('click', async () => {
    await InsertSale();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'F4') {
        const myModalEl = document.getElementById('pesquisaProdutoModal');
        if (myModalEl) {
            const modal = new bootstrap.Modal(myModalEl);
            modal.show();
        }
    }
    if (e.key === 'F8') {
        const myModalEl = document.getElementById('pesquisaProdutoModal');
        if (myModalEl) {
            const modal = new bootstrap.Modal(myModalEl);
            modal.hide();
        }
    }
    if (e.key === 'F9') {
        insertItemButton?.click();
    }
});

log('Sale.js carregado com sucesso');
