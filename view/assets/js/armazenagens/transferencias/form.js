/**
 * Formulário de solicitação de transferência
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formTransferencia');
    if (!form) {
        return;
    }

    const selectItem = document.getElementById('selectItem');
    const selectOrigem = document.getElementById('selectOrigem');
    const selectDestino = document.getElementById('selectDestino');
    const quantidadeDisponivel = document.getElementById('quantidadeDisponivel');
    const quantidade = document.getElementById('quantidadeTransferencia');

    if (selectItem) {
        selectItem.addEventListener('change', atualizarItem);
        atualizarItem();
    }

    if (selectOrigem && selectDestino) {
        selectDestino.addEventListener('change', validarOrigemDestino);
        selectOrigem.addEventListener('change', validarOrigemDestino);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!validarFormulario()) {
            return;
        }

        const formData = new FormData(form);

        Swal.fire({
            title: 'Salvando...',
            text: 'Aguarde enquanto solicitamos a transferência.',
            allowOutsideClick: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message || 'Transferência solicitada.',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        window.location.href = data.redirect || buildUrl('/transferencias');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message || data.error || 'Erro ao solicitar transferência.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao enviar formulário.',
                    confirmButtonText: 'OK'
                });
            });
    });

    function atualizarItem() {
        const option = selectItem.options[selectItem.selectedIndex];
        if (!option || !option.value) {
            if (quantidadeDisponivel) {
                quantidadeDisponivel.value = '';
            }
            return;
        }

        if (quantidadeDisponivel) {
            quantidadeDisponivel.value = option.dataset.qty || '';
        }
        if (quantidade && !quantidade.value) {
            quantidade.value = option.dataset.qty || '';
        }
        if (selectOrigem && option.dataset.origem) {
            selectOrigem.value = option.dataset.origem;
        }
    }

    function validarOrigemDestino() {
        if (selectOrigem.value && selectDestino.value && selectOrigem.value === selectDestino.value) {
            Swal.fire('Atenção!', 'A armazenagem de origem e destino não podem ser iguais.', 'warning');
            selectDestino.value = '';
        }
    }

    function validarFormulario() {
        if (!selectItem.value) {
            Swal.fire('Erro!', 'Selecione um item.', 'error');
            return false;
        }
        if (!selectOrigem.value || !selectDestino.value) {
            Swal.fire('Erro!', 'Selecione origem e destino.', 'error');
            return false;
        }
        if ((parseInt(quantidade.value, 10) || 0) <= 0) {
            Swal.fire('Erro!', 'A quantidade deve ser maior que zero.', 'error');
            return false;
        }
        return true;
    }
});
