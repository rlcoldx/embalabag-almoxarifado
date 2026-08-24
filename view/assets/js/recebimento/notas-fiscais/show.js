/**
 * Ações da página de detalhe da nota fiscal
 */

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-gerar-etiqueta').forEach(function (botao) {
        botao.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            if (!itemId) {
                return;
            }

            const formData = new FormData();
            formData.append('item_id', itemId);

            Swal.fire({
                title: 'Gerando etiqueta...',
                allowOutsideClick: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            fetch(buildUrl('/recebimento/etiquetas/gerar'), {
                method: 'POST',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Etiqueta gerada',
                            text: data.message || 'Etiqueta criada com sucesso.',
                            confirmButtonText: 'Ver etiqueta'
                        }).then(function () {
                            window.location.href = data.redirect || buildUrl('/recebimento/etiquetas');
                        });
                    } else {
                        Swal.fire('Erro', data.message || data.error || 'Erro ao gerar etiqueta.', 'error');
                    }
                })
                .catch(function () {
                    Swal.fire('Erro', 'Erro ao gerar etiqueta.', 'error');
                });
        });
    });
});

window.excluirNotaFiscal = function (id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir esta nota fiscal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }

        fetch(buildUrl('/recebimento/notas-fiscais/delete/' + id), { method: 'GET' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.href = data.redirect || buildUrl('/recebimento/notas-fiscais');
                    return;
                }
                Swal.fire('Erro', data.message || data.error || 'Erro ao excluir.', 'error');
            });
    });
};
