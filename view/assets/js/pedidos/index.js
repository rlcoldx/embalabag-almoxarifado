/**
 * Listagem de pedidos
 */

document.addEventListener('DOMContentLoaded', function() {
    window.excluirPedido = function(id) {
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Deseja realmente excluir este pedido?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar',
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            fetch(buildUrl('/pedidos/delete/' + id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message || 'Pedido excluído.',
                            confirmButtonText: 'OK',
                        }).then(function() {
                            reloadDataTable('pedidos');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.error || 'Erro ao excluir pedido.',
                        });
                    }
                });
        });
    };
});
