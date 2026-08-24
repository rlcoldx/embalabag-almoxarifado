/**
 * Ações da listagem de Etiquetas (datatable do sistema)
 */

window.excluirEtiqueta = function(id) {
    Swal.fire({
        title: 'Excluir etiqueta?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch(buildUrl('/recebimento/etiquetas/delete/' + id), { method: 'GET' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire('Excluído', data.message || 'Etiqueta excluída.', 'success');
                    reloadDataTable('etiquetas');
                } else {
                    Swal.fire('Erro', data.message || data.error || 'Erro ao excluir.', 'error');
                }
            });
    });
};

window.imprimirEtiqueta = function(id) {
    fetch(buildUrl('/recebimento/etiquetas/imprimir/' + id), { method: 'GET' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                Swal.fire('Sucesso', data.message || 'Etiqueta enviada para impressão.', 'success');
                reloadDataTable('etiquetas');
            } else {
                Swal.fire('Erro', data.message || data.error || 'Erro ao imprimir.', 'error');
            }
        });
};

window.aplicarEtiqueta = function(id) {
    Swal.fire({
        title: 'Aplicar etiqueta?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Aplicar',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch(buildUrl('/recebimento/etiquetas/aplicar/' + id), { method: 'GET' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire('Sucesso', data.message || 'Etiqueta aplicada.', 'success');
                    reloadDataTable('etiquetas');
                } else {
                    Swal.fire('Erro', data.message || data.error || 'Erro ao aplicar.', 'error');
                }
            });
    });
};
