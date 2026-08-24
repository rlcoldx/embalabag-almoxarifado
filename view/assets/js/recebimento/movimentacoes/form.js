/**
 * Formulário compartilhado de movimentações (create, edit, put-away, transferência)
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formMovimentacao');
    if (!form) {
        return;
    }

    const selectNota = document.getElementById('selectNotaFiscal');
    const selectItem = document.getElementById('selectItemNF');
    const selectOrigem = document.getElementById('selectArmazenagemOrigem');
    const selectDestino = document.getElementById('selectArmazenagemDestino');
    const tipoSelect = document.getElementById('tipoMovimentacao');
    const quantidadeDisponivel = document.getElementById('quantidadeDisponivel');
    const quantidadeMovimentada = document.getElementById('quantidadeMovimentada') || form.querySelector('[name="quantidade_movimentada"]');
    const capacidadeDestino = document.getElementById('capacidadeDestino');
    const infoProduto = document.getElementById('infoProduto');
    const detalhesProduto = document.getElementById('detalhesProduto');
    const divOrigem = document.getElementById('divArmazenagemOrigem');

    if (selectNota && selectItem) {
        selectNota.addEventListener('change', filtrarItensPorNota);
    }

    if (selectItem) {
        selectItem.addEventListener('change', atualizarDadosItem);
        atualizarDadosItem();
    }

    if (selectDestino && capacidadeDestino) {
        selectDestino.addEventListener('change', atualizarCapacidadeDestino);
        atualizarCapacidadeDestino();
    }

    if (tipoSelect) {
        tipoSelect.addEventListener('change', atualizarVisibilidadeOrigem);
        atualizarVisibilidadeOrigem();
    }

    if (quantidadeMovimentada && quantidadeDisponivel) {
        quantidadeMovimentada.addEventListener('blur', function () {
            const disponivel = parseInt(quantidadeDisponivel.value, 10) || 0;
            const movimentada = parseInt(this.value, 10) || 0;

            if (disponivel > 0 && movimentada > disponivel) {
                Swal.fire('Atenção!', 'A quantidade movimentada não pode ser maior que a disponível.', 'warning');
                this.value = disponivel;
            }
        });
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
            text: 'Aguarde enquanto processamos a movimentação.',
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
                        text: data.message || 'Operação realizada com sucesso.',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        window.location.href = data.redirect || buildUrl('/recebimento/movimentacoes');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message || data.error || 'Erro ao salvar movimentação.',
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

    function filtrarItensPorNota() {
        const notaId = selectNota.value;
        Array.from(selectItem.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = Boolean(notaId) && option.dataset.nf !== notaId;
        });

        const selecionada = selectItem.options[selectItem.selectedIndex];
        if (selecionada && selecionada.hidden) {
            selectItem.value = '';
            atualizarDadosItem();
        }
    }

    function atualizarDadosItem() {
        const option = selectItem ? selectItem.options[selectItem.selectedIndex] : null;
        if (!option || !option.value) {
            if (quantidadeDisponivel) {
                quantidadeDisponivel.value = '';
            }
            if (infoProduto) {
                infoProduto.style.display = 'none';
            }
            return;
        }

        const qty = option.dataset.qty || '';
        if (quantidadeDisponivel) {
            quantidadeDisponivel.value = qty;
        }
        if (quantidadeMovimentada && !quantidadeMovimentada.value) {
            quantidadeMovimentada.value = qty;
        }

        if (selectOrigem && option.dataset.origem && !selectOrigem.value) {
            selectOrigem.value = option.dataset.origem;
        }

        if (infoProduto && detalhesProduto) {
            detalhesProduto.textContent = option.dataset.produto || option.textContent;
            infoProduto.style.display = 'block';
        }
    }

    function atualizarCapacidadeDestino() {
        if (!selectDestino || !capacidadeDestino) {
            return;
        }
        const option = selectDestino.options[selectDestino.selectedIndex];
        capacidadeDestino.value = option && option.dataset.capacidade ? option.dataset.capacidade : '';
    }

    function atualizarVisibilidadeOrigem() {
        if (!divOrigem || !tipoSelect) {
            return;
        }
        const precisaOrigem = ['transferencia', 'reposicao'].indexOf(tipoSelect.value) !== -1;
        divOrigem.style.display = precisaOrigem || tipoSelect.value === 'ajuste' ? '' : (tipoSelect.value === 'put_away' ? 'none' : '');
        if (selectOrigem && tipoSelect.value === 'put_away') {
            selectOrigem.value = '';
        }
        if (selectOrigem) {
            selectOrigem.required = precisaOrigem;
        }
    }

    function validarOrigemDestino() {
        if (!selectOrigem || !selectDestino) {
            return;
        }
        if (selectOrigem.value && selectDestino.value && selectOrigem.value === selectDestino.value) {
            Swal.fire('Atenção!', 'A armazenagem de origem e destino não podem ser iguais.', 'warning');
            selectDestino.value = '';
            atualizarCapacidadeDestino();
        }
    }

    function validarFormulario() {
        const itemField = form.querySelector('[name="item_nf_id"]');
        const tipoField = form.querySelector('[name="tipo_movimentacao"]');
        const itemId = selectItem ? selectItem.value : (itemField ? itemField.value : '');
        const destino = selectDestino ? selectDestino.value : '';
        const quantidade = parseInt(quantidadeMovimentada ? quantidadeMovimentada.value : 0, 10) || 0;
        const tipo = tipoSelect ? tipoSelect.value : (tipoField ? tipoField.value : '');

        if (!itemId) {
            Swal.fire('Erro!', 'Selecione um item da nota fiscal.', 'error');
            return false;
        }

        if (!destino) {
            Swal.fire('Erro!', 'Selecione a armazenagem de destino.', 'error');
            return false;
        }

        if (quantidade <= 0) {
            Swal.fire('Erro!', 'A quantidade movimentada deve ser maior que zero.', 'error');
            return false;
        }

        if (!tipo) {
            Swal.fire('Erro!', 'Selecione o tipo de movimentação.', 'error');
            return false;
        }

        if (['transferencia', 'reposicao'].indexOf(tipo) !== -1 && selectOrigem && !selectOrigem.value) {
            Swal.fire('Erro!', 'Selecione a armazenagem de origem.', 'error');
            return false;
        }

        return true;
    }
});
