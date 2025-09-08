/**
 * Script para listagem de Etiquetas Internas
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar DataTable
    inicializarDataTable();
    
    // Inicializar filtros
    inicializarFiltros();
});

/**
 * Inicializa o DataTable das etiquetas
 */
function inicializarDataTable() {
    const table = $('#tableEtiquetas').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: DOMAIN + '/datatable/etiquetas',
            type: 'GET',
            data: function(d) {
                d.filtros = obterFiltros();
            }
        },
        columns: [
            { data: 'id', width: '5%' },
            { data: 'codigo', width: '10%' },
            { data: 'tipo_etiqueta', width: '10%' },
            { data: 'conteudo', width: '20%' },
            { data: 'status', width: '10%' },
            { data: 'usuario_criacao', width: '10%' },
            { data: 'data_impressao', width: '12%' },
            { data: 'created_at', width: '13%' },
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
    window.tableEtiquetas = table;
}

/**
 * Inicializa os filtros da página
 */
function inicializarFiltros() {
    // Botão filtrar
    document.getElementById('btnFiltrar').addEventListener('click', function() {
        if (window.tableEtiquetas) {
            window.tableEtiquetas.ajax.reload();
        }
    });
    
    // Botão limpar filtros
    document.getElementById('btnLimparFiltros').addEventListener('click', function() {
        limparFiltros();
        if (window.tableEtiquetas) {
            window.tableEtiquetas.ajax.reload();
        }
    });
    
    // Filtrar ao pressionar Enter nos campos de texto
    document.getElementById('filtroCodigo').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (window.tableEtiquetas) {
                window.tableEtiquetas.ajax.reload();
            }
        }
    });
}

/**
 * Obtém os valores dos filtros
 */
function obterFiltros() {
    return {
        codigo: document.getElementById('filtroCodigo').value,
        tipo_etiqueta: document.getElementById('filtroTipo').value,
        status: document.getElementById('filtroStatus').value,
        data_inicio: document.getElementById('filtroDataInicio').value,
        data_fim: document.getElementById('filtroDataFim').value
    };
}

/**
 * Limpa todos os filtros
 */
function limparFiltros() {
    document.getElementById('filtroCodigo').value = '';
    document.getElementById('filtroTipo').value = '';
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
}

/**
 * Imprime uma etiqueta
 */
function imprimirEtiqueta(id) {
    Swal.fire({
        title: 'Confirmar impressão',
        text: 'Tem certeza que deseja imprimir esta etiqueta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, imprimir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl('/recebimento/etiquetas/' + id + '/imprimir'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Impressa!',
                        'A etiqueta foi enviada para impressão.',
                        'success'
                    ).then(() => {
                        if (window.tableEtiquetas) {
                            window.tableEtiquetas.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao imprimir etiqueta.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao imprimir etiqueta.',
                    'error'
                );
            });
        }
    });
}

/**
 * Visualiza uma etiqueta
 */
function visualizarEtiqueta(id) {
    // Abrir modal ou nova janela para visualizar a etiqueta
    window.open(DOMAIN + '/recebimento/etiquetas/' + id + '/visualizar', '_blank');
}

/**
 * Exclui uma etiqueta
 */
function excluirEtiqueta(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta etiqueta?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl('/recebimento/etiquetas/' + id + '/delete'), {
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
                        'A etiqueta foi excluída com sucesso.',
                        'success'
                    ).then(() => {
                        if (window.tableEtiquetas) {
                            window.tableEtiquetas.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.message || 'Erro ao excluir a etiqueta.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire(
                    'Erro!',
                    'Erro ao excluir a etiqueta.',
                    'error'
                );
            });
        }
    });
}

/**
 * Gera QR Code para uma etiqueta
 */
function gerarQRCode(id) {
    fetch(buildUrl('/recebimento/etiquetas/' + id + '/qr-code'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'QR Code Gerado',
                text: 'QR Code gerado com sucesso!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                if (window.tableEtiquetas) {
                    window.tableEtiquetas.ajax.reload();
                }
            });
        } else {
            Swal.fire(
                'Erro!',
                data.message || 'Erro ao gerar QR Code.',
                'error'
            );
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire(
            'Erro!',
            'Erro ao gerar QR Code.',
            'error'
        );
    });
} 