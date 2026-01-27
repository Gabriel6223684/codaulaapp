
const tabela = new $('#tabela').DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    stateSave: true,
    processing: true,
    serverSide: true,
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json',
        searchPlaceholder: 'Digite sua pesquisa...'
    },
    ajax: {
        url: '/cliente/listacliente', // URL correta
        type: 'POST'
    },
    columns: [
        { title: "Código" },
        { title: "Nome completo" },
        { title: "CPF" },
        { title: "Email" },
        { title: "Ações", orderable: false, searchable: false }
    ]
});
