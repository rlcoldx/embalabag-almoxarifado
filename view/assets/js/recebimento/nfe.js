/**
 * JavaScript para Notas Fiscais Eletrônicas
 */

class NfeManager {
    constructor() {
        this.itens = [];
        this.currentItemIndex = -1;
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupDateDefaults();
    }

    bindEvents() {
        // Botão para abrir modal de criação
        $(document).on('click', '#btnNovaNfe', () => {
            this.openCreateModal();
        });

        // Buscar pedido
        $(document).on('click', '#btnBuscarPedido', () => {
            this.buscarPedido();
        });

        // Adicionar item
        $(document).on('click', '#btnAddItem', () => {
            this.openAddItemModal();
        });

        // Confirmar item
        $(document).on('click', '#btnConfirmarItem', () => {
            this.confirmarItem();
        });

        // Buscar produto por SKU
        $(document).on('input', '#item_sku', (e) => {
            this.buscarProdutoPorSKU(e.target.value);
        });

        // Selecionar variação
        $(document).on('change', '#item_variacao', (e) => {
            this.selecionarVariacao(e.target.value);
        });

        // Form de criação de NF-e
        $(document).on('submit', '#formNfeCreate', (e) => {
            e.preventDefault();
            this.submitNfe();
        });

        // Form de adicionar item
        $(document).on('submit', '#formAddItem', (e) => {
            e.preventDefault();
            this.confirmarItem();
        });

        // Remover item
        $(document).on('click', '.btn-remove-item', (e) => {
            const index = $(e.target).closest('.item-row').data('index');
            this.removerItem(index);
        });

        // Editar item
        $(document).on('click', '.btn-edit-item', (e) => {
            const index = $(e.target).closest('.item-row').data('index');
            this.editarItem(index);
        });

        // Scanner de código de barras
        $(document).on('click', '#btnScanSKU', () => {
            this.openScanner();
        });
    }

    setupDateDefaults() {
        // Definir data de emissão como hoje
        const today = new Date().toISOString().split('T')[0];
        $('#data_emissao').val(today);
    }

    openCreateModal() {
        $('#modalNfeCreate').modal('show');
        this.limparFormulario();
        this.atualizarItensContainer();
    }

    openAddItemModal() {
        $('#modalAddItem').modal('show');
        this.limparFormularioItem();
    }

    buscarPedido() {
        let DOMAIN = document.body.getAttribute('data-domain') || '';
        const numeroPedido = $('#numero_pedido').val().trim();
        
        if (!numeroPedido) {
            this.showAlert('Digite o número do pedido', 'warning');
            return;
        }

        $.ajax({
            url: `${DOMAIN}/recebimento/nfe/buscar-pedido`,
            method: 'GET',
            data: { numero: numeroPedido },
            success: (response) => {
                if (response.success) {
                    this.pedidoEncontrado(response.pedido);
                } else {
                    this.showAlert(response.error, 'danger');
                }
            },
            error: () => {
                this.showAlert('Erro ao buscar pedido', 'danger');
            }
        });
    }

    pedidoEncontrado(pedido) {
        $('#pedido_id').val(pedido.id);
        $('#pedidoNumero').text(pedido.numero_pedido || pedido.codigo);
        $('#pedidoInfo').show();
        
        this.showAlert('Pedido encontrado com sucesso!', 'success');
    }

    buscarProdutoPorSKU(sku) {
        let DOMAIN = document.body.getAttribute('data-domain') || '';
        if (sku.length < 3) return;

        $.ajax({
            url: `${DOMAIN}/produtos/buscar/${sku}`,
            method: 'GET',
            success: (response) => {
                if (response.success && response.produto) {
                    this.produtoEncontrado(response.produto);
                } else {
                    this.limparProduto();
                }
            },
            error: () => {
                this.limparProduto();
            }
        });
    }

    produtoEncontrado(produto) {
        $('#item_produto').val(produto.nome);
        $('#item_produto_id').val(produto.id);
        
        // Carregar variações
        this.carregarVariacoes(produto.id);
    }

    limparProduto() {
        $('#item_produto').val('');
        $('#item_produto_id').val('');
        $('#item_variacao').html('<option value="">Selecione a variação</option>');
        $('#item_variacao_id').val('');
    }

    carregarVariacoes(produtoId) {
        let DOMAIN = document.body.getAttribute('data-domain') || '';
        $.ajax({
            url: `${DOMAIN}/produtos/variacoes/${produtoId}`,
            method: 'GET',
            success: (response) => {
                if (response.success && response.variacoes) {
                    this.preencherVariacoes(response.variacoes);
                }
            },
            error: () => {
                this.showAlert('Erro ao carregar variações', 'danger');
            }
        });
    }

    preencherVariacoes(variacoes) {
        let options = '<option value="">Selecione a variação</option>';
        
        variacoes.forEach(variacao => {
            const label = `${variacao.tamanho} - ${variacao.cor}`;
            options += `<option value="${variacao.id}" data-variacao='${JSON.stringify(variacao)}'>${label}</option>`;
        });
        
        $('#item_variacao').html(options);
    }

    selecionarVariacao(variacaoId) {
        if (!variacaoId) {
            $('#item_variacao_id').val('');
            return;
        }

        const option = $(`#item_variacao option[value="${variacaoId}"]`);
        const variacao = JSON.parse(option.data('variacao'));
        
        $('#item_variacao_id').val(variacao.id);
        
        // Preencher valor unitário se disponível
        if (variacao.preco) {
            $('#item_valor_unitario').val(variacao.preco);
        }
    }

    confirmarItem() {
        const formData = this.getFormDataItem();
        
        if (!this.validarItem(formData)) {
            return;
        }

        if (this.currentItemIndex >= 0) {
            // Editando item existente
            this.itens[this.currentItemIndex] = formData;
        } else {
            // Adicionando novo item
            this.itens.push(formData);
        }

        $('#modalAddItem').modal('hide');
        this.atualizarItensContainer();
        this.limparFormularioItem();
        this.currentItemIndex = -1;
        
        this.showAlert('Item adicionado com sucesso!', 'success');
    }

    getFormDataItem() {
        return {
            produto_id: $('#item_produto_id').val(),
            produto_nome: $('#item_produto').val(),
            variacao_id: $('#item_variacao_id').val(),
            variacao_label: $('#item_variacao option:selected').text(),
            quantidade: parseInt($('#item_quantidade').val()),
            valor_unitario: parseFloat($('#item_valor_unitario').val()),
            valor_total: parseInt($('#item_quantidade').val()) * parseFloat($('#item_valor_unitario').val())
        };
    }

    validarItem(data) {
        if (!data.produto_id) {
            this.showAlert('Selecione um produto', 'warning');
            return false;
        }
        
        if (!data.variacao_id) {
            this.showAlert('Selecione uma variação', 'warning');
            return false;
        }
        
        if (!data.quantidade || data.quantidade <= 0) {
            this.showAlert('Quantidade deve ser maior que zero', 'warning');
            return false;
        }
        
        if (!data.valor_unitario || data.valor_unitario <= 0) {
            this.showAlert('Valor unitário deve ser maior que zero', 'warning');
            return false;
        }
        
        return true;
    }

    editarItem(index) {
        const item = this.itens[index];
        this.currentItemIndex = index;
        
        $('#item_produto').val(item.produto_nome);
        $('#item_produto_id').val(item.produto_id);
        $('#item_quantidade').val(item.quantidade);
        $('#item_valor_unitario').val(item.valor_unitario);
        
        // Carregar variações e selecionar a atual
        this.carregarVariacoes(item.produto_id);
        
        setTimeout(() => {
            $('#item_variacao').val(item.variacao_id);
            $('#item_variacao_id').val(item.variacao_id);
        }, 100);
        
        $('#modalAddItem').modal('show');
    }

    removerItem(index) {
        this.itens.splice(index, 1);
        this.atualizarItensContainer();
        this.showAlert('Item removido com sucesso!', 'success');
    }

    atualizarItensContainer() {
        const container = $('#itensContainer');
        const semItens = $('#semItens');
        
        if (this.itens.length === 0) {
            container.hide();
            semItens.show();
            return;
        }
        
        container.show();
        semItens.hide();
        
        let html = '';
        this.itens.forEach((item, index) => {
            html += `
                <div class="item-row border rounded p-3 mb-2" data-index="${index}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>${item.produto_nome}</strong>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-secondary">${item.variacao_label}</span>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-info">Qtd: ${item.quantidade}</span>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-success">R$ ${item.valor_unitario.toFixed(2)}</span>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-primary">R$ ${item.valor_total.toFixed(2)}</span>
                        </div>
                        <div class="col-md-1">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-edit-item" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-remove-item" title="Remover">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
        
        // Atualizar valor total
        this.atualizarValorTotal();
    }

    atualizarValorTotal() {
        const total = this.itens.reduce((sum, item) => sum + item.valor_total, 0);
        $('#valor_total').val(total.toFixed(2));
    }

    limparFormulario() {
        $('#formNfeCreate')[0].reset();
        this.itens = [];
        this.currentItemIndex = -1;
        this.setupDateDefaults();
        this.atualizarItensContainer();
        $('#pedidoInfo').hide();
        $('#pedido_id').val('');
    }

    limparFormularioItem() {
        $('#formAddItem')[0].reset();
        $('#item_produto').val('');
        $('#item_produto_id').val('');
        $('#item_variacao').html('<option value="">Selecione a variação</option>');
        $('#item_variacao_id').val('');
    }

    submitNfe() {
        if (this.itens.length === 0) {
            this.showAlert('Adicione pelo menos um item à NF-e', 'warning');
            return;
        }

        // Adicionar itens ao form
        this.itens.forEach((item, index) => {
            $('<input>').attr({
                type: 'hidden',
                name: `itens[${index}][produto_id]`,
                value: item.produto_id
            }).appendTo('#formNfeCreate');
            
            $('<input>').attr({
                type: 'hidden',
                name: `itens[${index}][variacao_id]`,
                value: item.variacao_id
            }).appendTo('#formNfeCreate');
            
            $('<input>').attr({
                type: 'hidden',
                name: `itens[${index}][quantidade]`,
                value: item.quantidade
            }).appendTo('#formNfeCreate');
            
            $('<input>').attr({
                type: 'hidden',
                name: `itens[${index}][valor_unitario]`,
                value: item.valor_unitario
            }).appendTo('#formNfeCreate');
        });

        // Enviar form
        $.ajax({
            url: $('#formNfeCreate').attr('action'),
            method: 'POST',
            data: $('#formNfeCreate').serialize(),
            success: (response) => {
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    this.showAlert(response.error, 'danger');
                }
            },
            error: () => {
                this.showAlert('Erro ao salvar NF-e', 'danger');
            }
        });
    }

    openScanner() {
        // Implementar scanner de código de barras
        // Por enquanto, apenas foca no campo SKU
        $('#item_sku').focus();
        this.showAlert('Digite o SKU ou use um leitor de código de barras', 'info');
    }

    showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remover alertas anteriores
        $('.alert').remove();
        
        // Adicionar novo alerta
        $('body').prepend(alertHtml);
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
}

// Inicializar quando documento estiver pronto
$(document).ready(() => {
    window.nfeManager = new NfeManager();
});
