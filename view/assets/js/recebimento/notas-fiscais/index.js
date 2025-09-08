/**
 * Script para listagem de Notas Fiscais
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar DataTable
    inicializarDataTable();
    
    // Inicializar filtros
    inicializarFiltros();
});

/**
 * Inicializa o DataTable das notas fiscais
 */
function inicializarDataTable() {
    const table = $('#tableNotasFiscais').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: DOMAIN + '/datatable/notas-fiscais',
            type: 'GET',
            data: function(d) {
                d.filtros = obterFiltros();
            }
        },
        columns: [
            { data: 'id', width: '5%' },
            { data: 'numero', width: '10%' },
            { data: 'fornecedor', width: '15%' },
            { data: 'data_emissao', width: '10%' },
            { data: 'valor_total', width: '10%' },
            { data: 'status', width: '10%' },
            { data: 'usuario_recebimento', width: '10%' },
            { data: 'data_recebimento', width: '10%' },
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
    window.tableNotasFiscais = table;
}

/**
 * Inicializa os filtros da página
 */
function inicializarFiltros() {
    // Botão filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        if (window.tableNotasFiscais) {
            window.tableNotasFiscais.ajax.reload();
        }
    });
    
    // Botão limpar filtros
    document.getElementById('btnLimparFiltros').addEventListener('click', function() {
        limparFiltros();
        if (window.tableNotasFiscais) {
            window.tableNotasFiscais.ajax.reload();
        }
    });
    
    // Filtrar ao pressionar Enter nos campos de texto
    document.getElementById('filtroNumero').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (window.tableNotasFiscais) {
                window.tableNotasFiscais.ajax.reload();
            }
        }
    });
    
    document.getElementById('filtroFornecedor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (window.tableNotasFiscais) {
                window.tableNotasFiscais.ajax.reload();
            }
        }
    });
}

/**
 * Obtém os valores dos filtros
 */
function obterFiltros() {
    return {
        numero: document.getElementById('filtroNumero').value,
        fornecedor: document.getElementById('filtroFornecedor').value,
        status: document.getElementById('filtroStatus').value,
        data_inicio: document.getElementById('filtroDataInicio').value,
        data_fim: document.getElementById('filtroDataFim').value
    };
}

/**
 * Limpa todos os filtros
 */
function limparFiltros() {
    document.getElementById('filtroNumero').value = '';
    document.getElementById('filtroFornecedor').value = '';
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
}

/**
 * Exclui uma nota fiscal
 */
function excluirNotaFiscal(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta nota fiscal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl('/recebimento/notas-fiscais/' + id + '/delete'), {
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
                        'A nota fiscal foi excluída com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableNotasFiscais) {
                            window.tableNotasFiscais.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao excluir a nota fiscal.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao excluir a nota fiscal.',
                    'error'
                );
            });
        }
    });
}

/**
 * Marca uma nota fiscal como recebida
 */
function receberNotaFiscal(id) {
    Swal.fire({
        title: 'Confirmar recebimento',
        text: 'Tem certeza que deseja marcar esta nota fiscal como recebida?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, receber!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl('/recebimento/notas-fiscais/' + id + '/receber'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Recebida!',
                        'A nota fiscal foi marcada como recebida.',
                        'success'
                    ).then(() => {
                        if (window.tableNotasFiscais) {
                            window.tableNotasFiscais.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao marcar como recebida.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao marcar como recebida.',
                    'error'
                );
            });
        }
    });
} 