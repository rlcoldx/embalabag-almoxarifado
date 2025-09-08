/**
 * Script para listagem de Conferências
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar DataTable
    inicializarDataTable();
    
    // Inicializar filtros
    inicializarFiltros();
});

/**
 * Inicializa o DataTable das conferências
 */
function inicializarDataTable() {
    const table = $('#tableConferencias').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: DOMAIN + '/datatable/conferencias',
            type: 'GET',
            data: function(d) {
                d.filtros = obterFiltros();
            }
        },
        columns: [
            { data: 'id', width: '5%' },
            { data: 'produto', width: '15%' },
            { data: 'numero_nf', width: '10%' },
            { data: 'fornecedor', width: '15%' },
            { data: 'quantidade_esperada', width: '8%' },
            { data: 'quantidade_recebida', width: '8%' },
            { data: 'quantidade_avariada', width: '8%' },
            { data: 'status_qualidade', width: '10%' },
            { data: 'usuario_conferente', width: '10%' },
            { data: 'data_conferencia', width: '11%' },
            { data: 'acoes', width: '20%', orderable: false }
        ],
        order: [[0, 'desc']],
        language: {
            url: DOMAIN + '/view/assets/js/vendors/datatables/js/Portuguese-Brasil.json'
        },
        responsive: true,
        pageLength: 25
    });
    
    // Armazenar referência da tabela
    window.tableConferencias = table;
}

/**
 * Inicializa os filtros da página
 */
function inicializarFiltros() {
    // Botão filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        if (window.tableConferencias) {
            window.tableConferencias.ajax.reload();
        }
    });
    
    // Botão limpar filtros
    document.getElementById('btnLimparFiltros').addEventListener('click', function() {
        limparFiltros();
        if (window.tableConferencias) {
            window.tableConferencias.ajax.reload();
        }
    });
    
    // Filtrar ao pressionar Enter nos campos de texto
    document.getElementById('filtroProduto').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (window.tableConferencias) {
                window.tableConferencias.ajax.reload();
            }
        }
    });
    
    document.getElementById('filtroNumeroNF').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (window.tableConferencias) {
                window.tableConferencias.ajax.reload();
            }
        }
    });
}

/**
 * Obtém os valores dos filtros
 */
function obterFiltros() {
    return {
        produto: document.getElementById('filtroProduto').value,
        numero_nf: document.getElementById('filtroNumeroNF').value,
        status_qualidade: document.getElementById('filtroStatusQualidade').value,
        data_inicio: document.getElementById('filtroDataInicio').value,
        data_fim: document.getElementById('filtroDataFim').value
    };
}

/**
 * Limpa todos os filtros
 */
function limparFiltros() {
    document.getElementById('filtroProduto').value = '';
    document.getElementById('filtroNumeroNF').value = '';
    document.getElementById('filtroStatusQualidade').value = '';
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
}

/**
 * Exclui uma conferência
 */
function excluirConferencia(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta conferência?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl('/recebimento/conferencia/' + id + '/delete'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Excluído!',
                        'A conferência foi excluída com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableConferencias) {
                            window.tableConferencias.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao excluir a conferência.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao excluir a conferência.',
                    'error'
                );
            });
        }
    });
}

/**
 * Aprova uma conferência
 */
function aprovarConferencia(id) {
    Swal.fire({
        title: 'Confirmar aprovação',
        text: 'Tem certeza que deseja aprovar esta conferência?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, aprovar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl('/recebimento/conferencia/' + id + '/aprovar'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Aprovada!',
                        'A conferência foi aprovada com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableConferencias) {
                            window.tableConferencias.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao aprovar conferência.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao aprovar conferência.',
                    'error'
                );
            });
        }
    });
} 