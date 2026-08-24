/**
 * Conferência de produtos — página conferir.twig + modal rápido
 */

let conferenciaNfeId = 0;
let modalConferenciaRapida = null;
let modalScannerConferencia = null;

document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('conferenciaPage');
    if (!page) {
        return;
    }

    conferenciaNfeId = parseInt(page.dataset.nfeId, 10) || 0;

    const modalRapidaEl = document.getElementById('modalConferenciaRapida');
    const modalScannerEl = document.getElementById('modalScannerConferencia');

    if (modalRapidaEl) {
        modalConferenciaRapida = new bootstrap.Modal(modalRapidaEl);
    }

    if (modalScannerEl) {
        modalScannerConferencia = new bootstrap.Modal(modalScannerEl);
    }

    const skuInput = document.getElementById('conferenciaRapidaSku');
    if (skuInput) {
        skuInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutoPorSku(skuInput.value.trim());
            }
        });
    }

    const qtdRecebida = document.getElementById('conferenciaRapidaQuantidadeRecebida');
    if (qtdRecebida) {
        qtdRecebida.addEventListener('input', function () {
            document.getElementById('conferenciaRapidaQuantidadeConferida').value = qtdRecebida.value;
        });
    }

    const form = document.getElementById('formConferencia');
    if (form) {
        form.addEventListener('submit', function () {
            atualizarResumo();
        });
    }

    atualizarResumo();
});

function atualizarQuantidadeConferida(input, index) {
    const row = input.closest('tr');
    if (!row) {
        return;
    }

    const conferidaInput = row.querySelector(`input[name="itens[${index}][quantidade_conferida]"]`);
    if (conferidaInput) {
        conferidaInput.value = input.value;
    }

    atualizarResumo();
}

function atualizarStatus(select, index) {
    atualizarResumo();
}

function aprovarTodos() {
    document.querySelectorAll('select[name*="[status_qualidade]"]').forEach(function (select) {
        select.value = 'aprovado';
    });
    atualizarResumo();
}

function rejeitarTodos() {
    document.querySelectorAll('select[name*="[status_qualidade]"]').forEach(function (select) {
        select.value = 'reprovado';
    });
    atualizarResumo();
}

function atualizarResumo() {
    const selects = document.querySelectorAll('select[name*="[status_qualidade]"]');
    let aprovados = 0;
    let rejeitados = 0;

    selects.forEach(function (select) {
        if (select.value === 'aprovado') {
            aprovados++;
        } else if (select.value === 'reprovado') {
            rejeitados++;
        }
    });

    const totalAprovados = document.getElementById('totalAprovados');
    const totalRejeitados = document.getElementById('totalRejeitados');

    if (totalAprovados) {
        totalAprovados.textContent = aprovados;
    }

    if (totalRejeitados) {
        totalRejeitados.textContent = rejeitados;
    }
}

function abrirConferenciaRapida() {
    limparFormularioConferenciaRapida();
    document.getElementById('conferenciaRapidaNfeId').value = conferenciaNfeId;

    if (modalConferenciaRapida) {
        modalConferenciaRapida.show();
        setTimeout(function () {
            document.getElementById('conferenciaRapidaSku').focus();
        }, 300);
    }
}

function limparFormularioConferenciaRapida() {
    const form = document.getElementById('formConferenciaRapida');
    if (!form) {
        return;
    }

    form.reset();
    document.getElementById('conferenciaRapidaNfeId').value = conferenciaNfeId;
    document.getElementById('conferenciaRapidaItemNfeId').value = '';
    document.getElementById('conferenciaRapidaProdutoId').value = '';
    document.getElementById('conferenciaRapidaVariacaoId').value = '';
    document.getElementById('conferenciaRapidaInfoProduto').style.display = 'none';
}

function abrirScannerConferencia() {
    const scannerInput = document.getElementById('scannerConferenciaInput');
    if (scannerInput) {
        scannerInput.value = '';
    }

    if (modalScannerConferencia) {
        modalScannerConferencia.show();
        setTimeout(function () {
            scannerInput.focus();
        }, 300);
    }
}

function confirmarCodigoScannerConferencia() {
    const codigo = document.getElementById('scannerConferenciaInput').value.trim();
    if (!codigo) {
        return;
    }

    document.getElementById('conferenciaRapidaSku').value = codigo;

    if (modalScannerConferencia) {
        modalScannerConferencia.hide();
    }

    buscarProdutoPorSku(codigo);
}

function buscarProdutoPorSku(sku) {
    if (!sku || conferenciaNfeId <= 0) {
        return;
    }

    fetch(buildUrl('/api/conferencia/buscar-item?nfe_id=' + conferenciaNfeId + '&sku=' + encodeURIComponent(sku)), {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success) {
                Swal.fire('Produto não encontrado', data.message || 'SKU não localizado nesta NF-e.', 'warning');
                return;
            }

            preencherModalConferenciaRapida(data.item);
        })
        .catch(function (error) {
            console.error('Erro ao buscar produto:', error);
            Swal.fire('Erro', 'Não foi possível buscar o produto.', 'error');
        });
}

function preencherModalConferenciaRapida(item) {
    document.getElementById('conferenciaRapidaItemNfeId').value = item.item_nfe_id;
    document.getElementById('conferenciaRapidaProdutoId').value = item.produto_id;
    document.getElementById('conferenciaRapidaVariacaoId').value = item.variacao_id;
    document.getElementById('conferenciaRapidaSku').value = item.sku;
    document.getElementById('conferenciaRapidaQuantidadePrevista').value = item.quantidade_prevista;

    const qtdRecebida = item.conferencia_existente
        ? item.conferencia_existente.quantidade_recebida
        : item.quantidade_prevista;

    document.getElementById('conferenciaRapidaQuantidadeRecebida').value = qtdRecebida;
    document.getElementById('conferenciaRapidaQuantidadeConferida').value = item.conferencia_existente
        ? item.conferencia_existente.quantidade_conferida
        : qtdRecebida;

    if (item.conferencia_existente) {
        document.getElementById('conferenciaRapidaStatusQualidade').value = item.conferencia_existente.status_qualidade || 'aprovado';
        document.getElementById('conferenciaRapidaStatusIntegridade').value = item.conferencia_existente.status_integridade || 'integro';
        document.getElementById('conferenciaRapidaObservacoesQualidade').value = item.conferencia_existente.observacoes_qualidade || '';
        document.getElementById('conferenciaRapidaObservacoesIntegridade').value = item.conferencia_existente.observacoes_integridade || '';
    } else {
        document.getElementById('conferenciaRapidaStatusQualidade').value = 'aprovado';
        document.getElementById('conferenciaRapidaStatusIntegridade').value = 'integro';
        document.getElementById('conferenciaRapidaObservacoesQualidade').value = '';
        document.getElementById('conferenciaRapidaObservacoesIntegridade').value = '';
    }

    document.getElementById('conferenciaRapidaNomeProduto').textContent = item.nome_produto;
    document.getElementById('conferenciaRapidaCategoria').textContent = item.categoria || '-';
    document.getElementById('conferenciaRapidaTamanho').textContent = item.tamanho || '-';
    document.getElementById('conferenciaRapidaCor').textContent = item.cor || '-';
    document.getElementById('conferenciaRapidaInfoProduto').style.display = 'block';
}

function salvarConferenciaRapida() {
    const payload = {
        nfe_id: conferenciaNfeId,
        item_nfe_id: document.getElementById('conferenciaRapidaItemNfeId').value,
        produto_id: document.getElementById('conferenciaRapidaProdutoId').value,
        variacao_id: document.getElementById('conferenciaRapidaVariacaoId').value,
        quantidade_prevista: document.getElementById('conferenciaRapidaQuantidadePrevista').value,
        quantidade_recebida: document.getElementById('conferenciaRapidaQuantidadeRecebida').value,
        quantidade_conferida: document.getElementById('conferenciaRapidaQuantidadeConferida').value,
        status_qualidade: document.getElementById('conferenciaRapidaStatusQualidade').value,
        status_integridade: document.getElementById('conferenciaRapidaStatusIntegridade').value,
        observacoes_qualidade: document.getElementById('conferenciaRapidaObservacoesQualidade').value,
        observacoes_integridade: document.getElementById('conferenciaRapidaObservacoesIntegridade').value,
        usuario_conferente_id: document.getElementById('usuario_conferente_id')
            ? document.getElementById('usuario_conferente_id').value
            : ''
    };

    if (!payload.produto_id || !payload.variacao_id) {
        Swal.fire('Atenção', 'Busque um produto pelo SKU antes de salvar.', 'warning');
        return;
    }

    if (!payload.status_qualidade || !payload.status_integridade) {
        Swal.fire('Atenção', 'Selecione o status de qualidade e integridade.', 'warning');
        return;
    }

    fetch(buildUrl('/api/conferencia/conferir-item'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success) {
                Swal.fire('Erro', data.message || 'Não foi possível salvar a conferência.', 'error');
                return;
            }

            atualizarLinhaTabelaConferencia(payload);
            atualizarResumo();

            if (modalConferenciaRapida) {
                modalConferenciaRapida.hide();
            }

            Swal.fire({
                icon: 'success',
                title: 'Conferência salva',
                text: data.nfe_conferida ? 'Todos os itens foram conferidos. NF-e finalizada.' : 'Produto conferido com sucesso.',
                timer: 2500,
                showConfirmButton: false
            });

            if (data.nfe_conferida) {
                setTimeout(function () {
                    window.location.href = buildUrl('/conferencia');
                }, 2600);
            }
        })
        .catch(function (error) {
            console.error('Erro ao salvar conferência:', error);
            Swal.fire('Erro', 'Não foi possível salvar a conferência.', 'error');
        });
}

function atualizarLinhaTabelaConferencia(payload) {
    const row = document.querySelector(
        `tr[data-item-nfe-id="${payload.item_nfe_id}"]`
    ) || document.querySelector(
        `tr[data-produto-id="${payload.produto_id}"][data-variacao-id="${payload.variacao_id}"]`
    );

    if (!row) {
        return;
    }

    const qtdRecebida = row.querySelector('input[name*="[quantidade_recebida]"]');
    const qtdConferida = row.querySelector('input[name*="[quantidade_conferida]"]');
    const statusQualidade = row.querySelector('select[name*="[status_qualidade]"]');
    const statusIntegridade = row.querySelector('select[name*="[status_integridade]"]');
    const obsQualidade = row.querySelector('textarea[name*="[observacoes_qualidade]"]');
    const obsIntegridade = row.querySelector('textarea[name*="[observacoes_integridade]"]');

    if (qtdRecebida) {
        qtdRecebida.value = payload.quantidade_recebida;
    }

    if (qtdConferida) {
        qtdConferida.value = payload.quantidade_conferida;
    }

    if (statusQualidade) {
        statusQualidade.value = payload.status_qualidade;
    }

    if (statusIntegridade) {
        statusIntegridade.value = payload.status_integridade;
    }

    if (obsQualidade) {
        obsQualidade.value = payload.observacoes_qualidade || '';
    }

    if (obsIntegridade) {
        obsIntegridade.value = payload.observacoes_integridade || '';
    }

    row.classList.add('table-success');
}
