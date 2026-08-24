/**
 * Ações da página de detalhe da transferência
 */

document.addEventListener('DOMContentLoaded', function () {
    const btnExecutar = document.getElementById('btnExecutarTransferencia');
    const btnCancelar = document.getElementById('btnCancelarTransferencia');

    if (btnExecutar) {
        btnExecutar.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Executar transferência?',
                text: 'O item será realocado para a armazenagem de destino.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Executar',
                cancelButtonText: 'Cancelar',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(buildUrl('/transferencias/execute/' + id), { method: 'POST' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Swal.fire('Sucesso', data.message || 'Transferência executada.', 'success').then(function () {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erro', data.message || data.error || 'Erro ao executar.', 'error');
                        }
                    });
            });
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Cancelar transferência?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Cancelar solicitação',
                cancelButtonText: 'Voltar',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(buildUrl('/transferencias/cancel/' + id), { method: 'POST' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Swal.fire('Cancelada', data.message || 'Transferência cancelada.', 'success').then(function () {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erro', data.message || data.error || 'Erro ao cancelar.', 'error');
                        }
                    });
            });
        });
    }
});
