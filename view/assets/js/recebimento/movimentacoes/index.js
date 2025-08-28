/**
 * Script para listagem de Movimentações Internas
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar DataTable
    inicializarDataTable();
    
    // Inicializar filtros
    inicializarFiltros();
});

/**
 * Inicializa o DataTable das movimentações
 */
function inicializarDataTable() {
    const table = $('#tableMovimentacoes').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: DOMAIN + '/datatable/movimentacoes',
            type: 'GET',
            data: function(d) {
                d.filtros = obterFiltros();
            }
        },
        columns: [
            { data: 'id', width: '5%' },
            { data: 'tipo_movimentacao', width: '10%' },
            { data: 'produto', width: '15%' },
            { data: 'armazenagem_origem', width: '10%' },
            { data: 'armazenagem_destino', width: '10%' },
            { data: 'quantidade_movimentada', width: '8%' },
            { data: 'status', width: '10%' },
            { data: 'usuario_movimentacao', width: '10%' },
            { data: 'data_movimentacao', width: '12%' },
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
    window.tableMovimentacoes = table;
}

/**
 * Inicializa os filtros da página
 */
function inicializarFiltros() {
    // Botão filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        if (window.tableMovimentacoes) {
            window.tableMovimentacoes.ajax.reload();
        }
    });
    
    // Botão limpar filtros
    document.getElementById('btnLimparFiltros').addEventListener('click', function() {
        limparFiltros();
        if (window.tableMovimentacoes) {
            window.tableMovimentacoes.ajax.reload();
        }
    });
    
    // Filtrar ao pressionar Enter nos campos de texto
    document.getElementById('filtroProduto').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (window.tableMovimentacoes) {
                window.tableMovimentacoes.ajax.reload();
            }
        }
    });
}

/**
 * Obtém os valores dos filtros
 */
function obterFiltros() {
    return {
        tipo: document.getElementById('filtroTipo').value,
        status: document.getElementById('filtroStatus').value,
        produto: document.getElementById('filtroProduto').value,
        data_inicio: document.getElementById('filtroDataInicio').value,
        data_fim: document.getElementById('filtroDataFim').value
    };
}

/**
 * Limpa todos os filtros
 */
function limparFiltros() {
    document.getElementById('filtroTipo').value = '';
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroProduto').value = '';
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
}

/**
 * Executa uma movimentação
 */
function executarMovimentacao(id) {
    Swal.fire({
        title: 'Confirmar execução',
        text: 'Tem certeza que deseja executar esta movimentação?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, executar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(DOMAIN + '/recebimento/movimentacoes/' + id + '/executar', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Executada!',
                        'A movimentação foi executada com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableMovimentacoes) {
                            window.tableMovimentacoes.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao executar movimentação.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao executar movimentação.',
                    'error'
                );
            });
        }
    });
}

/**
 * Cancela uma movimentação
 */
function cancelarMovimentacao(id) {
    Swal.fire({
        title: 'Confirmar cancelamento',
        text: 'Tem certeza que deseja cancelar esta movimentação?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, cancelar!',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(DOMAIN + '/recebimento/movimentacoes/' + id + '/cancelar', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Cancelada!',
                        'A movimentação foi cancelada com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableMovimentacoes) {
                            window.tableMovimentacoes.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao cancelar movimentação.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao cancelar movimentação.',
                    'error'
                );
            });
        }
    });
}

/**
 * Exclui uma movimentação
 */
function excluirMovimentacao(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta movimentação?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(DOMAIN + '/recebimento/movimentacoes/' + id + '/delete', {
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
                        'A movimentação foi excluída com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableMovimentacoes) {
                            window.tableMovimentacoes.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao excluir a movimentação.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao excluir a movimentação.',
                    'error'
                );
            });
        }
    });
} 