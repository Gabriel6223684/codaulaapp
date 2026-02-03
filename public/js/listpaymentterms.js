// listpaymentterms.js

// Tabela onde vamos renderizar os termos
const tableBody = document.querySelector('table tbody');

// Pega os termos do localStorage
let paymentTerms = JSON.parse(localStorage.getItem('paymentTerms')) || [];

// Função para renderizar tabela
function renderList() {
    tableBody.innerHTML = '';

    if (paymentTerms.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center">Nenhum termo de pagamento cadastrado</td>
            </tr>`;
        return;
    }

    paymentTerms.forEach((term, termIndex) => {
        term.parcelas.forEach((parcela, pIndex) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${term.codigo}</td>
                <td>${term.titulo}</td>
                <td>${parcela.parcela}x</td>
                <td>${parcela.parcela}x</td>
                <td>${parcela.intervalo}</td>
                <td>${parcela.vencimento}</td>
                <td>
                    <button class="btn btn-danger" data-term="${termIndex}" data-parcela="${pIndex}">Excluir</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    });
}

// Excluir parcela da lista
tableBody.addEventListener('click', (e) => {
    if (!e.target.classList.contains('btn-danger')) return;

    const termIndex = e.target.dataset.term;
    const parcelaIndex = e.target.dataset.parcela;

    paymentTerms[termIndex].parcelas.splice(parcelaIndex, 1);

    if (paymentTerms[termIndex].parcelas.length === 0) {
        paymentTerms.splice(termIndex, 1);
    }

    localStorage.setItem('paymentTerms', JSON.stringify(paymentTerms));
    renderList();
});

// Renderiza ao carregar a página
renderList();
