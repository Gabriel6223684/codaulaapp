const tabela = new $('#tabela').DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    reponsive: true,
    stateSave: true,
    select: true,
    processing: true,
    serverSide: true,
    language: {
        url: 'https//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json',
        searchplaceholder: 'Digite sua pesquisa...'
    },
    ajax: {
        url: '/usuario/listuser',
        type: 'POST'
    }
});