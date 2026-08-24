/**
 * Ações da página de detalhe da movimentação
 */

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnExecutarMovimentacao');
    if (!btn) {
        return;
    }

    btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');

        Swal.fire({
            title: 'Executar movimentação?',
            text: 'O item será alocado na armazenagem de destino.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Executar',
            cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            fetch(buildUrl('/recebimento/movimentacoes/executar/' + id), { method: 'GET' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        Swal.fire('Sucesso', data.message || 'Movimentação executada.', 'success').then(function () {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Erro', data.message || data.error || 'Erro ao executar.', 'error');
                    }
                });
        });
    });
});
