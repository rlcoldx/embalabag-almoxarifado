/**
 * JavaScript para os Modais de Armazém
 * Funcionalidades: Select2 com AJAX, carregamento de variações, estoque atual
 */
// Configuração global do Select2 para produtos
const select2Config = {
    ajax: {
        url: `${DOMAIN}/api/produtos/buscar`,
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                search: params.term
            };
        },
        processResults: function(data, params) {
            return {
                results: data.data.map(function(item) {
                    return {
                        id: item.id,
                        text: `${item.SKU} - ${item.nome}`,
                        data: item
                    };
                })
            };
        },
        cache: true
    },
    placeholder: 'Digite para buscar o produto...',
    minimumInputLength: 2,
    width: '100%',
    templateResult: formatProdutoOption,
    templateSelection: formatProdutoSelection,
    allowClear: true,
    closeOnSelect: true,
    escapeMarkup: function(markup) {
        return markup;
    }
};

/**
 * Formatar opção do Select2 para produtos
 */
function formatProdutoOption(produto) {
    if (produto.loading) return produto.text;
    if (produto.id === '') return produto.text;
    
    return $(`
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <div class="fw-semibold">${produto.data.SKU}</div>
                <div class="text-muted small">${produto.data.nome}</div>
            </div>
            <div class="text-end">
                <span class="badge bg-primary">${produto.data.categoria || 'N/A'}</span>
            </div>
        </div>
    `);
}

/**
 * Formatar seleção do Select2 para produtos
 */
function formatProdutoSelection(produto) {
    if (produto.id === '') return produto.text;
    return produto.data ? `${produto.data.SKU} - ${produto.data.nome}` : produto.text;
}



/**
 * Inicializar Select2 em todos os campos de produto
 */
function initializeSelect2Produtos() {
    $('.select2-ajax').each(function () {
        try {
            // Destruir Select2 existente se houver
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }

            // Determinar qual modal contém este campo
            const modal = $(this).closest('.modal');
            const modalId = modal.attr('id');

            // Configuração específica para cada modal com dropdownParent correto
            const config = {
                ...select2Config,
                dropdownParent: modal // ✅ SOLUÇÃO: manter dropdown dentro do modal
            };

            // Aplicar configuração
            $(this).select2(config);

            // Remover eventos anteriores para evitar duplicação
            $(this).off('select2:select select2:clear');

            // Evento quando um produto é selecionado
            $(this).on('select2:select', function (e) {
                const produto = e.params.data.data;
                const modalId = $(this).closest('.modal').attr('id');
                carregarVariacoesProduto(produto.id, modalId);
                exibirInfoProduto(produto, modalId);
            });

            // Evento quando a seleção é limpa
            $(this).on('select2:clear', function () {
                const modalId = $(this).closest('.modal').attr('id');
                limparCamposModal(modalId);
            });
        } catch (error) {
            // Erro silencioso
        }
    });
}

/**
 * Carregar variações de um produto via AJAX
 */
function carregarVariacoesProduto(produtoId, modalId) {
    let DOMAIN = document.body.getAttribute('data-domain') || '';
    const variacaoSelect = $(`#${modalId} select[name="variacao_id"]`);
    
    if (variacaoSelect.length === 0) {
        return;
    }
    
    // Habilitar campo de variação
    variacaoSelect.prop('disabled', false);
    
    // Mostrar loading
    variacaoSelect.html('<option value="">Carregando variações...</option>');
    
    // Fazer requisição AJAX
    $.ajax({
        url: `${DOMAIN}/api/produtos/variacoes/${produtoId}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let options = '<option value="">Selecione a variação</option>';
                
                response.data.forEach(function(variacao) {
                    const estoque = variacao.estoque || 0;
                    const cor = variacao.cor || 'N/A';
                    const tamanho = variacao.tamanho || 'N/A';
    
        options += `<option value="${variacao.id_produto}" 
                        data-estoque="${estoque}" 
                        data-cor="${cor}"
                        data-variacao-id="${variacao.id}">
                        ${cor} (Estoque: ${estoque})
                    </option>`;
                });
                
                variacaoSelect.html(options);
            } else {
                variacaoSelect.html('<option value="">Nenhuma variação encontrada</option>');
            }
        },
        error: function(xhr, status, error) {
            variacaoSelect.html('<option value="">Erro ao carregar variações</option>');
        }
    });
}

/**
 * Exibir informações do produto selecionado
 */
function exibirInfoProduto(produto, modalId) {
    // Mapear IDs corretos para cada modal
    let infoDiv, nomeId, categoriaId, id_produto, marcaId, materialId, valorId;
    
    if (modalId === 'modalNovaEntrada') {
        infoDiv = $(`#${modalId} #infoProdutoEntrada`);
        nomeId = '#infoNomeProdutoEntrada';
        categoriaId = '#infoCategoriaEntrada';
        id_produto = '#infoSkuEntrada';
        marcaId = '#infoMarcaEntrada';
        materialId = '#infoMaterialEntrada';
        valorId = '#infoValorEntrada';
    } else if (modalId === 'modalNovaSaida') {
        infoDiv = $(`#${modalId} #infoProdutoSaida`);
        nomeId = '#infoNomeProdutoSaida';
        categoriaId = '#infoCategoriaSaida';
        id_produto = '#infoSkuSaida';
        marcaId = '#infoMarcaSaida';
        materialId = '#infoMaterialSaida';
        valorId = '#infoValorSaida';
    } else if (modalId === 'modalMovimentacao') {
        infoDiv = $(`#${modalId} #infoProdutoMovimentacao`);
        nomeId = '#infoNomeProdutoMovimentacao';
        categoriaId = '#infoCategoriaMovimentacao';
        id_produto = '#infoSkuMovimentacao';
        marcaId = '#infoMarcaMovimentacao';
        materialId = '#infoMaterialMovimentacao';
        valorId = '#infoValorMovimentacao';
    }
    
    if (infoDiv && infoDiv.length > 0) {
        // Preencher informações básicas
        $(`#${modalId} ${nomeId}`).text(produto.nome);
        $(`#${modalId} ${categoriaId}`).text(produto.categoria || 'N/A');
        $(`#${modalId} ${id_produto}`).text(produto.id_produto);
        $(`#${modalId} ${marcaId}`).text(produto.marca || 'N/A');
        $(`#${modalId} ${materialId}`).text(produto.material || 'N/A');
        $(`#${modalId} ${valorId}`).text(produto.valor ? `R$ ${produto.valor}` : 'N/A');
        
        // Mostrar div de informações
        infoDiv.show();
    }
}

/**
 * Limpar campos do modal quando produto é desmarcado
 */
function limparCamposModal(modalId) {
    // Limpar campo de variação
    const variacaoSelect = $(`#${modalId} select[name="variacao_id"]`);
    variacaoSelect.prop('disabled', true);
    variacaoSelect.html('<option value="">Selecione primeiro o produto</option>');
    
    // Limpar campo de estoque atual
    $(`#${modalId} input[id*="EstoqueAtual"]`).val('');
    
    // Ocultar informações do produto baseado no modal
    if (modalId === 'modalNovaEntrada') {
        $(`#${modalId} #infoProdutoEntrada`).hide();
    } else if (modalId === 'modalNovaSaida') {
        $(`#${modalId} #infoProdutoSaida`).hide();
    } else if (modalId === 'modalMovimentacao') {
        $(`#${modalId} #infoProdutoMovimentacao`).hide();
    }
    
    // Limpar campo de quantidade
    $(`#${modalId} input[name="quantidade"]`).val('');
}

/**
 * Evento quando uma variação é selecionada
 */
function onVariacaoChange(modalId) {
    const variacaoSelect = $(`#${modalId} select[name="variacao_id"]`);
    const estoqueAtualInput = $(`#${modalId} input[id*="EstoqueAtual"]`);
    const quantidadeInput = $(`#${modalId} input[name="quantidade"]`);
    
    // Remover eventos anteriores para evitar duplicação
    variacaoSelect.off('change');
    
    variacaoSelect.on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const estoque = selectedOption.data('estoque') || 0;
        
        // Preencher estoque atual
        estoqueAtualInput.val(estoque);
        
        // Definir quantidade máxima para saídas
        if (modalId === 'modalNovaSaida') {
            quantidadeInput.attr('max', estoque);
            quantidadeInput.attr('placeholder', `Máximo: ${estoque}`);
        }
    });
}

/**
 * Inicializar todos os modais de armazém
 */
function initializeModaisArmazem() {
    try {
        // Configurar eventos de variação para cada modal
        onVariacaoChange('modalNovaEntrada');
        onVariacaoChange('modalNovaSaida');
        onVariacaoChange('modalMovimentacao');
        
        // Configurar validações específicas
        setupValidacoesModais();
    } catch (error) {
        // Erro silencioso
    }
}

/**
 * Configurar validações específicas dos modais
 */
function setupValidacoesModais() {
    // Validação para saídas (não pode exceder estoque)
    $('#modalNovaSaida input[name="quantidade"]').on('input', function() {
        const quantidade = parseInt($(this).val()) || 0;
        const estoqueAtual = parseInt($('#saidaEstoqueAtual').val()) || 0;
        
        if (quantidade > estoqueAtual) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).after(`<div class="invalid-feedback">Quantidade não pode exceder o estoque disponível (${estoqueAtual})</div>`);
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
}

/**
 * Abrir modal de Nova Entrada
 */
function abrirModalNovaEntrada(armazenagemId) {
    try {
        $('#entradaArmazenagemId').val(armazenagemId);
        $('#modalNovaEntrada').modal('show');
        
        // Inicializar Select2 após o modal estar visível
        $('#modalNovaEntrada').off('shown.bs.modal').on('shown.bs.modal', function() {
            setTimeout(function() {
                initializeSelect2Produtos();
            }, 200);
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao abrir modal de nova entrada'
        });
    }
}

/**
 * Abrir modal de Nova Saída
 */
function abrirModalNovaSaida(armazenagemId) {
    try {
        $('#saidaArmazenagemId').val(armazenagemId);
        $('#modalNovaSaida').modal('show');
        
        // Inicializar Select2 após o modal estar visível
        $('#modalNovaSaida').off('shown.bs.modal').on('shown.bs.modal', function() {
            setTimeout(function() {
                initializeSelect2Produtos();
            }, 200);
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao abrir modal de nova saída'
        });
    }
}

/**
 * Abrir modal de Movimentação
 */
function abrirModalMovimentacao(tipo, armazenagemId) {
    try {
        $('#movimentacaoTipo').val(tipo);
        $('#movimentacaoArmazenagemId').val(armazenagemId);
        
        // Atualizar título baseado no tipo
        let titulo = 'Nova Movimentação';
        if (tipo === 'entrada') titulo = 'Nova Entrada';
        if (tipo === 'saida') titulo = 'Nova Saída';
        
        $('#movimentacaoTitulo').text(titulo);
        $('#modalMovimentacao').modal('show');
        
        // Inicializar Select2 após o modal estar visível
        $('#modalMovimentacao').on('shown.bs.modal', function() {
            setTimeout(function() {
                initializeSelect2Produtos();
            }, 100);
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao abrir modal de movimentação'
        });
    }
}

// Inicializar quando o DOM estiver pronto
$(document).ready(function() {
    initializeModaisArmazem();
});

/**
 * Salvar entrada de estoque
 */
function salvarEntrada() {
    let DOMAIN = document.body.getAttribute('data-domain') || '';
    try {
        // Validar formulário
        const form = document.getElementById('formNovaEntrada');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Coletar dados do formulário
        const formData = new FormData(form);
        
        // ✅ CORRIGIDO: Pegar o variacao_id correto do data-attribute
        const variacaoSelect = document.querySelector('#modalNovaEntrada select[name="variacao_id"]');
        const selectedOption = variacaoSelect.options[variacaoSelect.selectedIndex];
        const variacaoId = selectedOption ? selectedOption.getAttribute('data-variacao-id') : '';
        
        const dados = {
            armazenagem_id: formData.get('armazenagem_id'),
            id_produto: formData.get('id_produto'),
            variacao_id: variacaoId,
            quantidade: parseInt(formData.get('quantidade')),
            motivo: formData.get('motivo'),
            documento_referencia: formData.get('documento_referencia'),
            observacoes: formData.get('observacoes'),
            tipo: 'entrada'
        };
        
        // Validar campos obrigatórios
        if (!dados.armazenagem_id || !dados.id_produto || !dados.variacao_id || !dados.quantidade) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos Obrigatórios',
                text: 'Por favor, preencha todos os campos obrigatórios.'
            });
            return;
        }
        
        // Mostrar loading no botão
        const btnSalvar = document.querySelector('#modalNovaEntrada button[onclick="salvarEntrada()"]');
        if (!btnSalvar) {
            return;
        }
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
        btnSalvar.disabled = true;
        
        // Fazer requisição AJAX
        $.ajax({
            url: `${DOMAIN}/api/movimentacoes/criar`,
            method: 'POST',
            dataType: 'json',
            data: dados,
            success: function(response) {
                if (response.success) {
                    // Sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Entrada de estoque registrada com sucesso!'
                    });
                    
                    // Fechar modal
                    $('#modalNovaEntrada').modal('hide');
                    
                    // Limpar formulário
                    form.reset();
                    limparCamposModal('modalNovaEntrada');
                    
                    // Recarregar página ou atualizar tabela
                    if (typeof recarregarArmazenagens === 'function') {
                        recarregarArmazenagens();
                    } else {
                        location.reload();
                    }
                } else {
                    // Erro da API
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao salvar entrada: ' + (response.error || 'Erro desconhecido')
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao salvar entrada de estoque. Tente novamente.'
                });
            },
            complete: function() {
                // Restaurar botão
                btnSalvar.innerHTML = textoOriginal;
                btnSalvar.disabled = false;
            }
        });
        
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao processar entrada de estoque. Tente novamente.'
        });
    }
}

/**
 * Salvar saída de estoque
 */
function salvarSaida() {
    let DOMAIN = document.body.getAttribute('data-domain') || '';
    try {
        // Validar formulário
        const form = document.getElementById('formNovaSaida');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Coletar dados do formulário
        const formData = new FormData(form);
        
                 // ✅ CORRIGIDO: Pegar o variacao_id correto do data-attribute
         const variacaoSelect = document.querySelector('#modalNovaSaida select[name="variacao_id"]');
         const selectedOption = variacaoSelect.options[variacaoSelect.selectedIndex];
         const variacaoId = selectedOption ? selectedOption.getAttribute('data-variacao-id') : '';
         
         const dados = {
             armazenagem_id: formData.get('armazenagem_id'),
             id_produto: formData.get('id_produto'),
             variacao_id: variacaoId,
             quantidade: parseInt(formData.get('quantidade')),
             motivo: formData.get('motivo'),
             documento_referencia: formData.get('documento_referencia'),
             observacoes: formData.get('observacoes'),
             tipo: 'saida'
         };
        
        // Validar campos obrigatórios
        if (!dados.armazenagem_id || !dados.id_produto || !dados.variacao_id || !dados.quantidade) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos Obrigatórios',
                text: 'Por favor, preencha todos os campos obrigatórios.'
            });
            return;
        }
        
        // Validar se quantidade não excede estoque
        const estoqueAtual = parseInt(document.getElementById('saidaEstoqueAtual').value) || 0;
        if (dados.quantidade > estoqueAtual) {
            Swal.fire({
                icon: 'warning',
                title: 'Quantidade Inválida',
                text: 'Quantidade não pode exceder o estoque disponível.'
            });
            return;
        }
        
        // Mostrar loading no botão
        const btnSalvar = document.querySelector('#modalNovaSaida button[onclick="salvarSaida()"]');
        if (!btnSalvar) {
            return;
        }
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
        btnSalvar.disabled = true;
        
        // Fazer requisição AJAX
        $.ajax({
            url: `${DOMAIN}/api/movimentacoes/criar`,
            method: 'POST',
            dataType: 'json',
            data: dados,
            success: function(response) {
                if (response.success) {
                    // Sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Saída de estoque registrada com sucesso!'
                    });
                    
                    // Fechar modal
                    $('#modalNovaSaida').modal('hide');
                    
                    // Limpar formulário
                    form.reset();
                    limparCamposModal('modalNovaSaida');
                    
                    // Recarregar página ou atualizar tabela
                    if (typeof recarregarArmazenagens === 'function') {
                        recarregarArmazenagens();
                    } else {
                        location.reload();
                    }
                } else {
                    // Erro da API
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao salvar saída: ' + (response.error || 'Erro desconhecido')
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao salvar saída de estoque. Tente novamente.'
                });
            },
            complete: function() {
                // Restaurar botão
                btnSalvar.innerHTML = textoOriginal;
                btnSalvar.disabled = false;
            }
        });
        
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao processar saída de estoque. Tente novamente.'
        });
    }
}

/**
 * Salvar movimentação de estoque
 */
function salvarMovimentacao() {
    let DOMAIN = document.body.getAttribute('data-domain') || '';
    try {
        // Validar formulário
        const form = document.getElementById('formMovimentacao');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Coletar dados do formulário
        const formData = new FormData(form);
        
        // ✅ CORRIGIDO: Pegar o variacao_id correto do data-attribute
        const variacaoSelect = document.querySelector('#modalMovimentacao select[name="variacao_id"]');
        const selectedOption = variacaoSelect.options[variacaoSelect.selectedIndex];
        const variacaoId = selectedOption ? selectedOption.getAttribute('data-variacao-id') : '';
         
        const dados = {
            armazenagem_origem_id: formData.get('armazenagem_origem_id'),
            armazenagem_destino_id: formData.get('armazenagem_destino_id'),
            id_produto: formData.get('id_produto'),
            variacao_id: variacaoId,
            quantidade: parseInt(formData.get('quantidade')),
            motivo: formData.get('motivo'),
            documento_referencia: formData.get('documento_referencia'),
            observacoes: formData.get('observacoes'),
            tipo: 'movimentacao'
        };
        
        // Validar campos obrigatórios
        if (!dados.armazenagem_id || !dados.id_produto || !dados.variacao_id || !dados.quantidade) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos Obrigatórios',
                text: 'Por favor, preencha todos os campos obrigatórios.'
            });
            return;
        }
        
        // Validar se armazenagens são diferentes
        if (dados.armazenagem_origem_id === dados.armazenagem_destino_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Armazenagens Iguais',
                text: 'A armazenagem de origem deve ser diferente da armazenagem de destino.'
            });
            return;
        }
        
        // Mostrar loading no botão
        const btnSalvar = document.querySelector('#modalMovimentacao button[onclick="salvarMovimentacao()"]');
        if (!btnSalvar) {
            return;
        }
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
        btnSalvar.disabled = true;
        
        // Fazer requisição AJAX
        $.ajax({
            url: `${DOMAIN}/api/movimentacoes/criar`,
            method: 'POST',
            dataType: 'json',
            data: dados,
            success: function(response) {
                if (response.success) {
                    // Sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Movimentação de estoque registrada com sucesso!'
                    });
                    
                    // Fechar modal
                    $('#modalMovimentacao').modal('hide');
                    
                    // Limpar formulário
                    form.reset();
                    limparCamposModal('modalMovimentacao');
                    
                    // Recarregar página ou atualizar tabela
                    if (typeof recarregarArmazenagens === 'function') {
                        recarregarArmazenagens();
                    } else {
                        location.reload();
                    }
                } else {
                    // Erro da API
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao salvar movimentação: ' + (response.error || 'Erro desconhecido')
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao salvar movimentação de estoque. Tente novamente.'
                });
            },
            complete: function() {
                // Restaurar botão
                btnSalvar.innerHTML = textoOriginal;
                btnSalvar.disabled = false;
            }
        });
        
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao processar movimentação de estoque. Tente novamente.'
        });
    }
}

// Exportar funções para uso global
window.ModaisArmazem = {
    abrirNovaEntrada: abrirModalNovaEntrada,
    abrirNovaSaida: abrirModalNovaSaida,
    abrirMovimentacao: abrirModalMovimentacao,
    salvarEntrada: salvarEntrada,
    salvarSaida: salvarSaida,
    salvarMovimentacao: salvarMovimentacao,
    initialize: initializeModaisArmazem
};

ModaisArmazem.initialize();