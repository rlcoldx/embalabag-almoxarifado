/**
 * Ações da listagem de Movimentações (datatable do sistema)
 */

window.executarMovimentacao = function(id) {
    Swal.fire({
        title: 'Executar movimentação?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Executar',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch(buildUrl('/recebimento/movimentacoes/executar/' + id), { method: 'GET' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire('Sucesso', data.message || 'Movimentação executada.', 'success');
                    reloadDataTable('movimentacoes');
                } else {
                    Swal.fire('Erro', data.message || data.error || 'Erro ao executar.', 'error');
                }
            });
    });
};

window.excluirMovimentacao = function(id) {
    Swal.fire({
        title: 'Excluir movimentação?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch(buildUrl('/recebimento/movimentacoes/delete/' + id), { method: 'GET' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire('Excluído', data.message || 'Movimentação excluída.', 'success');
                    reloadDataTable('movimentacoes');
                } else {
                    Swal.fire('Erro', data.message || data.error || 'Erro ao excluir.', 'error');
                }
            });
    });
};
