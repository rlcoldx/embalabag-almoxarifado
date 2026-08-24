/**
 * Detalhes do pedido — aprovar / cancelar
 */

function initPedidoShow(options) {
    const btnAprovar = document.getElementById('btnAprovarPedido');
    const btnCancelar = document.getElementById('btnCancelarPedido');
    const pedidoId = options.pedidoId;

    if (btnAprovar) {
        btnAprovar.addEventListener('click', function() {
            Swal.fire({
                title: 'Aprovar pedido?',
                text: 'O pedido será marcado como aprovado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Aprovar',
                cancelButtonText: 'Cancelar',
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(buildUrl('/pedidos/aprovar/' + pedidoId), { method: 'POST' })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Sucesso', text: data.message }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Erro', text: data.error || 'Erro ao aprovar.' });
                        }
                    });
            });
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            Swal.fire({
                title: 'Cancelar pedido?',
                text: 'Esta ação marcará o pedido como cancelado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Cancelar pedido',
                cancelButtonText: 'Voltar',
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(buildUrl('/pedidos/cancelar/' + pedidoId), { method: 'POST' })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Sucesso', text: data.message }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Erro', text: data.error || 'Erro ao cancelar.' });
                        }
                    });
            });
        });
    }
}
