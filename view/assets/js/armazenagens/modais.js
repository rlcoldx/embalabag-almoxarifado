/**
 * JavaScript para os Modais de Armazém
 * Funcionalidades: Select2 com AJAX, carregamento de variações, estoque atual
 */
// Configuração global do Select2 para produtos
document.addEventListener('DOMContentLoaded', function() {
    
    const select2Config = {
        ajax: {
            url: buildUrl('/api/produtos/buscar'),
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
                    <div class="small">${produto.data.nome}</div>
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
            url: buildUrl(`/api/produtos/variacoes/${produtoId}`),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let options = '<option value="">Selecione a variação</option>';
                    
                    response.data.forEach(function(variacao) {
                        const estoque = variacao.estoque || 0;
                        const cor = variacao.cor || 'N/A';
                        const tamanho = variacao.tamanho || 'N/A';
        
            options += `<option value="${variacao.id}" 
                            data-estoque="${estoque}" 
                            data-cor="${cor}"
                            data-id-produto="${variacao.id_produto}"
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
        } else if (modalId === 'modalTransferencia') {
            infoDiv = $(`#${modalId} #infoProdutoTransferencia`);
            nomeId = '#infoNomeProdutoTransferencia';
            categoriaId = '#infoCategoriaTransferencia';
            id_produto = '#infoSkuTransferencia';
            marcaId = '#infoMarcaTransferencia';
            materialId = '#infoMaterialTransferencia';
            valorId = '#infoValorTransferencia';
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
        } else if (modalId === 'modalTransferencia') {
            $(`#${modalId} #infoProdutoTransferencia`).hide();
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
            onVariacaoChange('modalTransferencia');
            
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

    /**
     * Abrir modal de Transferência
     */
    function abrirModalTransferencia(armazenagemId) {
        try {
            $('#transferenciaArmazenagemOrigem').val(armazenagemId);
            $('#modalTransferencia').modal('show');
            
            // Inicializar Select2 após o modal estar visível
            $('#modalTransferencia').off('shown.bs.modal').on('shown.bs.modal', function() {
                setTimeout(function() {
                    initializeSelect2Produtos();
                    carregarArmazenagensDestino(armazenagemId);
                }, 200);
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao abrir modal de transferência'
            });
        }
    }

    /**
     * Carregar armazenagens de destino (excluindo a origem)
     */
    function carregarArmazenagensDestino(armazenagemOrigemId) {
        const selectDestino = $('#transferenciaArmazenagemDestino');
        
        // Limpar e mostrar loading
        selectDestino.html('<option value="">Carregando armazenagens...</option>');
        
        // Buscar armazenagens
        $.ajax({
            url: buildUrl('/api/armazenagens/listar'),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.armazenagens) {
                    let options = '<option value="">Selecione o destino</option>';
                    
                    response.armazenagens.forEach(function(armazenagem) {
                        // Excluir a armazenagem de origem
                        if (armazenagem.id != armazenagemOrigemId && armazenagem.status === 'ativo') {
                            const espacoDisponivel = armazenagem.capacidade_maxima - armazenagem.capacidade_atual;
                            options += `<option value="${armazenagem.id}" 
                                            data-capacidade-maxima="${armazenagem.capacidade_maxima}"
                                            data-capacidade-atual="${armazenagem.capacidade_atual}"
                                            data-espaco-disponivel="${espacoDisponivel}">
                                            ${armazenagem.codigo} - ${armazenagem.descricao || armazenagem.nome} 
                                            (Espaço: ${espacoDisponivel}/${armazenagem.capacidade_maxima})
                                        </option>`;
                        }
                    });
                    
                    selectDestino.html(options);
                    
                    // Configurar evento de mudança para validar espaço
                    configurarValidacaoEspacoTransferencia();
                } else {
                    selectDestino.html('<option value="">Erro ao carregar armazenagens</option>');
                }
            },
            error: function(xhr, status, error) {
                selectDestino.html('<option value="">Erro ao carregar armazenagens</option>');
            }
        });
    }

    /**
     * Configurar validação de espaço para transferência
     */
    function configurarValidacaoEspacoTransferencia() {
        const selectDestino = $('#transferenciaArmazenagemDestino');
        const inputQuantidade = $('#transferenciaQuantidade');
        const btnSalvar = $('#modalTransferencia button[onclick="salvarTransferencia()"]');
        const alertValidacao = $('#validacoesTransferencia');
        const listaValidacoes = $('#listaValidacoes');
        
        // Remover eventos anteriores
        selectDestino.off('change');
        inputQuantidade.off('input');
        
        // Evento quando armazenagem destino muda
        selectDestino.on('change', function() {
            validarEspacoTransferencia();
        });
        
        // Evento quando quantidade muda
        inputQuantidade.on('input', function() {
            validarEspacoTransferencia();
        });
        
        function validarEspacoTransferencia() {
            const selectedOption = selectDestino.find('option:selected');
            const quantidade = parseInt(inputQuantidade.val()) || 0;
            
            // Limpar validações anteriores
            listaValidacoes.empty();
            alertValidacao.hide();
            btnSalvar.prop('disabled', false);
            
            if (selectedOption.val() && quantidade > 0) {
                const espacoDisponivel = parseInt(selectedOption.data('espaco-disponivel')) || 0;
                const capacidadeMaxima = parseInt(selectedOption.data('capacidade-maxima')) || 0;
                const capacidadeAtual = parseInt(selectedOption.data('capacidade-atual')) || 0;
                
                if (quantidade > espacoDisponivel) {
                    listaValidacoes.append(`
                        <li><strong>Quantidade (${quantidade}) excede o espaço disponível (${espacoDisponivel})</strong></li>
                        <li>Capacidade atual: ${capacidadeAtual}/${capacidadeMaxima}</li>
                        <li>Espaço necessário: ${quantidade} unidades</li>
                        <li>Espaço disponível: ${espacoDisponivel} unidades</li>
                    `);
                    alertValidacao.show();
                    btnSalvar.prop('disabled', true);
                } else {
                    // Espaço suficiente - atualizar informações
                    const novaCapacidade = capacidadeAtual + quantidade;
                    const novoEspacoDisponivel = espacoDisponivel - quantidade;
                    $('#infoCapacidadeDestino').text(`${novaCapacidade}/${capacidadeMaxima} (${novoEspacoDisponivel} livres)`);
                }
            } else if (!selectedOption.val()) {
                btnSalvar.prop('disabled', true);
            } else if (quantidade <= 0) {
                btnSalvar.prop('disabled', true);
            }
        }
    }

    // Inicializar quando o DOM estiver pronto
    $(document).ready(function() {
        initializeModaisArmazem();
    });

    window.aplicarProdutoPorSku = function (sku, modalId, selectSelector) {
        if (!sku) {
            return;
        }

        fetch(buildUrl('/api/produtos/sku/' + encodeURIComponent(sku)))
            .then(function (response) { return response.json(); })
            .then(function (data) {
                const produto = data.data || data.produto;
                if (!data.success || !produto) {
                    Swal.fire('Produto não encontrado', data.error || data.message || 'SKU não localizado.', 'warning');
                    return;
                }

                const $select = $(selectSelector);
                if ($select.length && $select.hasClass('select2-hidden-accessible')) {
                    const label = (produto.SKU || sku) + ' - ' + (produto.nome || '');
                    const option = new Option(label, produto.id, true, true);
                    $select.empty().append(option).trigger('change');
                    $select.trigger({
                        type: 'select2:select',
                        params: { data: { id: produto.id, text: label, data: produto } }
                    });
                    return;
                }

                if ($select.length) {
                    $select.val(produto.id);
                }

                carregarVariacoesProduto(produto.id, modalId);
                exibirInfoProduto(produto, modalId);
            })
            .catch(function () {
                Swal.fire('Erro', 'Erro ao buscar produto pelo código.', 'error');
            });
    };

    // Exportar funções para uso global
    window.ModaisArmazem = {
        abrirNovaEntrada: abrirModalNovaEntrada,
        abrirNovaSaida: abrirModalNovaSaida,
        abrirMovimentacao: abrirModalMovimentacao,
        abrirTransferencia: abrirModalTransferencia,
        salvarEntrada: salvarEntrada,
        salvarSaida: salvarSaida,
        salvarMovimentacao: salvarMovimentacao,
        initialize: initializeModaisArmazem
    };

    ModaisArmazem.initialize();
});