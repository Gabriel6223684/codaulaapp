// ELEMENTOS
const insertPaymentoTermsButton = document.getElementById('insertPaymentoTermsButton');
const insertInstallmentButton = document.getElementById('insertInstallmentButton');

const parcelaInput = document.getElementById('parcela');
const intervaloInput = document.getElementById('intervalo');
const vencimentoInput = document.getElementById('vencimento_incial_parcela');

const installmentsTbody = document.querySelector('table tbody'); // tabela temporária de parcelas

const codigoInput = document.getElementById('codigo');
const tituloInput = document.getElementById('titulo');

let installments = [];
let paymentTerms = JSON.parse(localStorage.getItem('paymentTerms')) || []; // pega dados do storage

// Função para renderizar tabela temporária de parcelas
function renderInstallments() {
    installmentsTbody.innerHTML = '';

    if (installments.length === 0) {
        installmentsTbody.innerHTML = `<tr><td colspan="5">Nenhuma parcela adicionada</td></tr>`;
        return;
    }

    installments.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.parcela}x</td>
            <td>${item.intervalo} dias</td>
            <td>${item.vencimento} dias</td>
            <td>
                <button class="btn btn-danger" data-index="${index}">Excluir</button>
            </td>
        `;
        installmentsTbody.appendChild(tr);
    });
}

// Adicionar parcela
insertInstallmentButton.addEventListener('click', () => {
    const parcela = parcelaInput.value.trim();
    const intervalo = intervaloInput.value.trim();
    const vencimento = vencimentoInput.value.trim() || 0;

    if (!parcela || !intervalo) {
        alert('Informe quantidade de parcelas e intervalo');
        return;
    }

    installments.push({
        parcela: Number(parcela),
        intervalo: Number(intervalo),
        vencimento: Number(vencimento)
    });

    renderInstallments();

    parcelaInput.value = '';
    intervaloInput.value = '';
    vencimentoInput.value = '';
});

// Remover parcela da tabela temporária
installmentsTbody.addEventListener('click', (e) => {
    if (!e.target.classList.contains('btn-danger')) return;
    const index = e.target.dataset.index;
    installments.splice(index, 1);
    renderInstallments();
});

// Função para renderizar tabela principal de termos de pagamento
function renderPaymentTerms() {
    let mainTable = document.querySelector('#paymentTermsTable');
    if (!mainTable) {
        mainTable = document.createElement('table');
        mainTable.id = 'paymentTermsTable';
        mainTable.className = 'table table-striped table-hover table-bordered';
        mainTable.style.width = '100%'; // ocupa toda a largura, evita barra
        mainTable.style.marginTop = '20px';
        mainTable.innerHTML = `
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Condição do pagamento</th>
                    <th>Parcelamento</th>
                    <th>Intervalo em dias</th>
                    <th>Vencimento inicial</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;
        // Inserir depois do form
        const form = document.getElementById('form');
        form.parentNode.insertBefore(mainTable, form.nextSibling);
    }

    const tbody = mainTable.querySelector('tbody');
    tbody.innerHTML = '';

    paymentTerms.forEach((term, index) => {
        term.parcelas.forEach((parcela, pIndex) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${term.codigo}</td>
                <td>${term.titulo}</td>
                <td>${parcela.parcela}x</td>
                <td>${parcela.intervalo} dias</td>
                <td>${parcela.vencimento} dias</td>
                <td>
                    <button class="btn btn-danger" data-term="${index}" data-parcela="${pIndex}">Excluir</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

// Salvar termo de pagamento
insertPaymentoTermsButton.addEventListener('click', () => {
    const codigo = codigoInput.value;
    const titulo = tituloInput.value;

    if (!codigo || !titulo) {
        alert('Informe o código e título do pagamento.');
        return;
    }

    if (installments.length === 0) {
        alert('Adicione pelo menos uma parcela.');
        return;
    }

    paymentTerms.push({
        codigo,
        titulo,
        parcelas: [...installments]
    });

    localStorage.setItem('paymentTerms', JSON.stringify(paymentTerms)); // salva no localStorage

    renderPaymentTerms();

    // Limpar formulário
    codigoInput.value = '';
    tituloInput.value = '';
    installments = [];
    renderInstallments();
});

// Excluir parcela da tabela principal
document.body.addEventListener('click', (e) => {
    if (!e.target.classList.contains('btn-danger')) return;

    const termIndex = e.target.dataset.term;
    const parcelaIndex = e.target.dataset.parcela;

    if (termIndex !== undefined && parcelaIndex !== undefined) {
        paymentTerms[termIndex].parcelas.splice(parcelaIndex, 1);

        if (paymentTerms[termIndex].parcelas.length === 0) {
            paymentTerms.splice(termIndex, 1);
        }

        localStorage.setItem('paymentTerms', JSON.stringify(paymentTerms)); // atualiza storage
        renderPaymentTerms();
    }
});

// Renderiza tabela ao carregar a página
renderPaymentTerms();
renderInstallments();
