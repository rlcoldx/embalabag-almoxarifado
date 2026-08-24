/**
 * Ações da página de detalhe da etiqueta
 */

document.addEventListener('DOMContentLoaded', function () {
    const btnImprimir = document.getElementById('btnImprimirEtiqueta');
    const btnAplicar = document.getElementById('btnAplicarEtiqueta');

    if (btnImprimir) {
        btnImprimir.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            fetch(buildUrl('/recebimento/etiquetas/imprimir/' + id), { method: 'GET' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        Swal.fire('Sucesso', data.message || 'Etiqueta enviada para impressão.', 'success').then(function () {
                            window.print();
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Erro', data.message || data.error || 'Erro ao imprimir.', 'error');
                    }
                });
        });
    }

    if (btnAplicar) {
        btnAplicar.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Aplicar etiqueta?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Aplicar',
                cancelButtonText: 'Cancelar',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                fetch(buildUrl('/recebimento/etiquetas/aplicar/' + id), { method: 'GET' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Swal.fire('Sucesso', data.message || 'Etiqueta aplicada.', 'success').then(function () {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erro', data.message || data.error || 'Erro ao aplicar.', 'error');
                        }
                    });
            });
        });
    }
});
