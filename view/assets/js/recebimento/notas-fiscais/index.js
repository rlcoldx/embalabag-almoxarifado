/**
 * Ações da listagem de Notas Fiscais (datatable do sistema)
 */

window.excluirNotaFiscal = function(id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta nota fiscal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch(buildUrl('/recebimento/notas-fiscais/delete/' + id), { method: 'GET' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire('Excluído!', data.message || 'Nota fiscal excluída.', 'success');
                    reloadDataTable('notas-fiscais');
                } else {
                    Swal.fire('Erro', data.message || data.error || 'Erro ao excluir.', 'error');
                }
            });
    });
};

window.receberNotaFiscal = function(id) {
    fetch(buildUrl('/recebimento/notas-fiscais/receber/' + id), { method: 'GET' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                Swal.fire('Sucesso', data.message || 'Nota marcada como recebida.', 'success');
                reloadDataTable('notas-fiscais');
            } else {
                Swal.fire('Erro', data.message || data.error || 'Erro ao receber.', 'error');
            }
        });
};
