/**
 * JavaScript para a página de detalhes da armazenagem
 */

document.addEventListener('DOMContentLoaded', function() {
    initTabs();
});

/**
 * Inicializar abas
 */
function initTabs() {
    const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
    
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const target = event.target.getAttribute('data-bs-target');
            
            // Carregar dados específicos da aba
            switch(target) {
                case '#movimentacoes-tab':
                    loadMovimentacoes();
                    break;
                case '#transferencias-tab':
                    loadTransferencias();
                    break;
                case '#historico-tab':
                    loadHistorico();
                    break;
            }
        });
    });
}

/**
 * Carregar movimentações da armazenagem
 */
function loadMovimentacoes() {
    const armazenagemId = getArmazenagemIdFromUrl();
    
    console.log('Carregando movimentações para armazenagem ID:', armazenagemId);
    console.log('URL atual:', window.location.pathname);
    
    if (!armazenagemId) {
        console.error('ID da armazenagem não encontrado');
        return;
    }
    
    fetch(buildUrl(`/api/armazenagens/movimentacoes/${armazenagemId}`))
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da API movimentações:', data);
            if (data.success) {
                renderMovimentacoes(data.movimentacoes);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar movimentações:', error);
        });
}

/**
 * Renderizar movimentações na tabela
 */
function renderMovimentacoes(movimentacoes) {
    const tbody = document.getElementById('movimentacoesTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    movimentacoes.forEach(mov => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDateTime(mov.data_movimentacao)}</td>
            <td><span class="badge ${mov.tipo_movimentacao === 'entrada' ? 'bg-success' : 'bg-danger'}">${mov.tipo_movimentacao}</span></td>
            <td>${mov.produto_nome}</td>
            <td>${mov.variacao_info}</td>
            <td>${mov.quantidade}</td>
            <td>${mov.motivo || '-'}</td>
            <td>${mov.usuario_nome}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Carregar transferências da armazenagem
 */
function loadTransferencias() {
    const armazenagemId = getArmazenagemIdFromUrl();
    
    console.log('Carregando transferências para armazenagem ID:', armazenagemId);
    
    if (!armazenagemId) {
        console.error('ID da armazenagem não encontrado');
        return;
    }
    
    fetch(buildUrl(`/api/armazenagens/transferencias/${armazenagemId}`))
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da API transferências:', data);
            if (data.success) {
                renderTransferencias(data.transferencias);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar transferências:', error);
        });
}

/**
 * Renderizar transferências na tabela
 */
function renderTransferencias(transferencias) {
    const tbody = document.getElementById('transferenciasTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    transferencias.forEach(transf => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDateTime(transf.data_transferencia)}</td>
            <td>${transf.produto_nome}</td>
            <td>${transf.variacao_info}</td>
            <td>${transf.quantidade}</td>
            <td>${transf.armazenagem_destino}</td>
            <td><span class="badge ${getStatusClass(transf.status)}">${transf.status}</span></td>
            <td>${transf.usuario_nome}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Carregar histórico da armazenagem
 */
function loadHistorico() {
    const armazenagemId = getArmazenagemIdFromUrl();
    
    console.log('Carregando histórico para armazenagem ID:', armazenagemId);
    
    if (!armazenagemId) {
        console.error('ID da armazenagem não encontrado');
        return;
    }
    
    fetch(buildUrl(`/api/armazenagens/historico/${armazenagemId}`))
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da API histórico:', data);
            if (data.success) {
                renderHistorico(data.historico);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar histórico:', error);
        });
}

/**
 * Renderizar histórico na timeline
 */
function renderHistorico(historico) {
    const container = document.getElementById('historicoTimeline');
    if (!container) return;
    
    container.innerHTML = '';
    
    historico.forEach(item => {
        const div = document.createElement('div');
        div.className = 'timeline-item';
        div.innerHTML = `
            <div class="timeline-marker ${getHistoricoColorClass(item.tipo)}">
                <i class="fas ${getHistoricoIconClass(item.tipo)}"></i>
            </div>
            <div class="timeline-content">
                <h6 class="timeline-title">${item.titulo}</h6>
                <p class="timeline-text">${item.descricao}</p>
                <small class="text-muted">${formatDateTime(item.data)}</small>
            </div>
        `;
        container.appendChild(div);
    });
}

/**
 * Abrir modal de movimentação
 */
function abrirModalMovimentacao(tipo) {
    const armazenagemId = getArmazenagemIdFromUrl();
    
    // Configurar modal
    document.getElementById('movimentacaoTipo').value = tipo;
    document.getElementById('movimentacaoArmazenagemId').value = armazenagemId;
    document.getElementById('movimentacaoTitulo').textContent = tipo === 'entrada' ? 'Nova Entrada' : 'Nova Saída';
    
    // Limpar formulário
    document.getElementById('formMovimentacao').reset();
    document.getElementById('infoProduto').style.display = 'none';
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalMovimentacao'));
    modal.show();
    
    // Configurar eventos
    configurarEventosMovimentacao();
}

/**
 * Abrir modal de transferência
 */
function abrirModalTransferencia() {
    const armazenagemId = getArmazenagemIdFromUrl();
    
    // Configurar modal
    document.getElementById('transferenciaArmazenagemOrigem').value = armazenagemId;
    document.getElementById('formTransferencia').reset();
    document.getElementById('infoProdutoTransferencia').style.display = 'none';
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalTransferencia'));
    modal.show();
    
    // Configurar eventos
    configurarEventosTransferencia();
}

/**
 * Ver detalhes do produto
 */
function verDetalhesProduto(produtoId, variacaoId) {
    // Buscar detalhes do produto
    fetch(buildUrl(`/api/produtos/${produtoId}/detalhes`))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.produto) {
                exibirModalProdutoDetalhes(data.produto);
            } else {
                Swal.fire('Erro', 'Produto não encontrado', 'error');
            }
        })
        .catch(error => {
            console.error('Erro ao buscar detalhes do produto:', error);
            Swal.fire('Erro', 'Erro interno do servidor', 'error');
        });
}

/**
 * Exibir modal de detalhes do produto
 */
function exibirModalProdutoDetalhes(produto) {
    // Preencher dados do modal
    document.getElementById('produtoDetalhesNome').textContent = produto.nome;
    document.getElementById('produtoDetalhesSku').textContent = produto.SKU;
    document.getElementById('produtoDetalhesCategoria').textContent = produto.categoria;
    document.getElementById('produtoDetalhesTamanho').textContent = produto.tamanho;
    document.getElementById('produtoDetalhesCor').textContent = produto.cor || 'N/A';
    document.getElementById('produtoDetalhesEstoque').textContent = produto.quantidade;
    document.getElementById('produtoDetalhesEstoqueMinimo').textContent = produto.estoque_minimo;
    
    // Calcular nível de estoque
    const nivelEstoque = (produto.quantidade / produto.estoque_minimo) * 100;
    const barraEstoque = document.getElementById('produtoDetalhesBarraEstoque');
    if (barraEstoque) {
        barraEstoque.style.width = `${Math.min(nivelEstoque, 100)}%`;
        barraEstoque.className = `progress-bar ${nivelEstoque < 50 ? 'bg-danger' : nivelEstoque < 80 ? 'bg-warning' : 'bg-success'}`;
    }
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalProdutoDetalhes'));
    modal.show();
    
    // Carregar dados das abas
    carregarDadosProdutoDetalhes(produto.id, produto.variacao_id);
}

/**
 * Carregar dados das abas do modal de produto
 */
function carregarDadosProdutoDetalhes(produtoId, variacaoId) {
    // Carregar movimentações
    fetch(buildUrl(`/api/produtos/${produtoId}/movimentacoes?variacao_id=${variacaoId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarMovimentacoesProduto(data.movimentacoes);
            }
        })
        .catch(error => console.error('Erro ao carregar movimentações:', error));
    
    // Carregar localizações
    fetch(buildUrl(`/api/produtos/${produtoId}/localizacoes?variacao_id=${variacaoId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarLocalizacoesProduto(data.localizacoes);
            }
        })
        .catch(error => console.error('Erro ao carregar localizações:', error));
}

/**
 * Renderizar movimentações do produto
 */
function renderizarMovimentacoesProduto(movimentacoes) {
    const tbody = document.getElementById('produtoDetalhesMovimentacoes');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    movimentacoes.forEach(mov => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDateTime(mov.data_movimentacao)}</td>
            <td><span class="badge ${mov.tipo_movimentacao === 'entrada' ? 'bg-success' : 'bg-danger'}">${mov.tipo_movimentacao}</span></td>
            <td>${mov.quantidade}</td>
            <td>${mov.armazenagem}</td>
            <td>${mov.motivo || '-'}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Renderizar localizações do produto
 */
function renderizarLocalizacoesProduto(localizacoes) {
    const tbody = document.getElementById('produtoDetalhesLocalizacoes');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    localizacoes.forEach(loc => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${loc.armazenagem}</td>
            <td>${loc.quantidade}</td>
            <td>${loc.ultima_movimentacao ? formatDateTime(loc.ultima_movimentacao) : '-'}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Movimentar produto
 */
function movimentarProduto(produtoId, variacaoId) {
    // Fechar modal de detalhes
    const modalDetalhes = bootstrap.Modal.getInstance(document.getElementById('modalProdutoDetalhes'));
    if (modalDetalhes) {
        modalDetalhes.hide();
    }
    
    // Abrir modal de movimentação
    setTimeout(() => {
        abrirModalMovimentacao('entrada');
        
        // Preencher dados do produto
        document.getElementById('movimentacaoProduto').value = produtoId;
        buscarProdutoPorSku();
        
        // Selecionar variação
        setTimeout(() => {
            const variacaoSelect = document.getElementById('movimentacaoVariacao');
            if (variacaoSelect) {
                variacaoSelect.value = variacaoId;
            }
        }, 500);
    }, 300);
}

/**
 * Nova movimentação do produto
 */
function novaMovimentacaoProduto(produtoId, variacaoId, tipo) {
    // Fechar modal de detalhes
    const modalDetalhes = bootstrap.Modal.getInstance(document.getElementById('modalProdutoDetalhes'));
    if (modalDetalhes) {
        modalDetalhes.hide();
    }
    
    // Abrir modal de movimentação
    setTimeout(() => {
        abrirModalMovimentacao(tipo);
        
        // Preencher dados do produto
        document.getElementById('movimentacaoProduto').value = produtoId;
        buscarProdutoPorSku();
        
        // Selecionar variação
        setTimeout(() => {
            const variacaoSelect = document.getElementById('movimentacaoVariacao');
            if (variacaoSelect) {
                variacaoSelect.value = variacaoId;
            }
        }, 500);
    }, 300);
}

/**
 * Editar produto
 */
function editarProduto(produtoId) {
    // Redirecionar para página de edição
    window.location.href = buildUrl(`/produtos/edit/${produtoId}`);
}

/**
 * Configurar eventos do modal de movimentação
 */
function configurarEventosMovimentacao() {
    // Buscar produto por SKU
    const skuInput = document.getElementById('movimentacaoProduto');
    
    if (skuInput) {
        skuInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarProdutoPorSku();
            }
        });
    }
    
    // Configurar scanner
    const scannerBtn = document.querySelector('#modalMovimentacao .btn-outline-primary');
    if (scannerBtn) {
        scannerBtn.addEventListener('click', () => abrirScanner('movimentacao'));
    }
    
    // Configurar envio do formulário
    const form = document.getElementById('formMovimentacao');
    if (form) {
        form.addEventListener('submit', salvarMovimentacao);
    }
}

/**
 * Buscar produto por SKU
 */
function buscarProdutoPorSku() {
    const sku = document.getElementById('movimentacaoProduto').value.trim();
    if (!sku) {
        Swal.fire('Erro', 'Digite o SKU do produto', 'error');
        return;
    }
    
    // Mostrar loading
    const infoProduto = document.getElementById('infoProduto');
    infoProduto.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando produto...</div>';
    infoProduto.style.display = 'block';
    
    // Fazer requisição AJAX
    fetch(buildUrl(`/api/produtos/buscar-por-sku?sku=${encodeURIComponent(sku)}`))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.produto) {
                exibirInfoProduto(data.produto);
                carregarVariacoes(data.produto.id);
            } else {
                infoProduto.innerHTML = '<div class="alert alert-warning">Produto não encontrado</div>';
            }
        })
        .catch(error => {
            console.error('Erro ao buscar produto:', error);
            infoProduto.innerHTML = '<div class="alert alert-danger">Erro ao buscar produto</div>';
        });
}

/**
 * Exibir informações do produto
 */
function exibirInfoProduto(produto) {
    const infoProduto = document.getElementById('infoProduto');
    infoProduto.innerHTML = `
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">${produto.nome}</h6>
                <p class="card-text">
                    <strong>SKU:</strong> ${produto.SKU}<br>
                    <strong>Categoria:</strong> ${produto.categoria}<br>
                    <strong>Status:</strong> <span class="badge bg-success">Ativo</span>
                </p>
            </div>
        </div>
    `;
}

/**
 * Carregar variações do produto
 */
function carregarVariacoes(produtoId) {
    const variacaoSelect = document.getElementById('movimentacaoVariacao');
    if (!variacaoSelect) return;
    
    // Limpar opções existentes
    variacaoSelect.innerHTML = '<option value="">Selecione a variação</option>';
    
    // Buscar variações do produto
    fetch(buildUrl(`/api/produtos/${produtoId}/variacoes`))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.variacoes) {
                data.variacoes.forEach(variacao => {
                    const option = document.createElement('option');
                    option.value = variacao.id;
                    option.textContent = `${variacao.tamanho || 'N/A'} - ${variacao.cor || 'Sem cor'} (Estoque: ${variacao.estoque || 0})`;
                    variacaoSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao carregar variações:', error);
        });
}

/**
 * Abrir scanner
 */
function abrirScanner(tipo) {
    // Simular abertura do scanner (implementar com biblioteca real)
    Swal.fire({
        title: 'Scanner de Código de Barras',
        html: `
            <div class="text-center">
                <i class="fas fa-barcode fa-3x text-primary mb-3"></i>
                <p>Posicione o código de barras na frente da câmera</p>
                <input type="text" id="codigoScanner" class="form-control" placeholder="Ou digite manualmente">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const codigo = document.getElementById('codigoScanner').value;
            if (!codigo) {
                Swal.showValidationMessage('Digite ou escaneie um código');
                return false;
            }
            return codigo;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            confirmarCodigoScanner(result.value, tipo);
        }
    });
}

/**
 * Confirmar código do scanner
 */
function confirmarCodigoScanner(codigo, tipo) {
    if (tipo === 'movimentacao') {
        document.getElementById('movimentacaoProduto').value = codigo;
        buscarProdutoPorSku();
    } else if (tipo === 'transferencia') {
        document.getElementById('transferenciaProduto').value = codigo;
        buscarProdutoTransferencia();
    }
}

/**
 * Salvar movimentação
 */
function salvarMovimentacao(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('formMovimentacao');
    const formData = new FormData(form);
    const dados = Object.fromEntries(formData.entries());
    
    // Adicionar tipo de movimentação
    dados.tipo_movimentacao = dados.tipo;
    
    // Validações
    if (!dados.sku || !dados.variacao_id || !dados.quantidade) {
        Swal.fire('Erro', 'Preencha todos os campos obrigatórios', 'error');
        return;
    }
    
    if (parseFloat(dados.quantidade) <= 0) {
        Swal.fire('Erro', 'A quantidade deve ser maior que zero', 'error');
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Salvando movimentação...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar dados
    fetch(buildUrl('/api/movimentacoes/criar'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso', 'Movimentação registrada com sucesso', 'success');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalMovimentacao'));
            modal.hide();
            
            // Recarregar dados da página
            location.reload();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao salvar movimentação', 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar movimentação:', error);
        Swal.fire('Erro', 'Erro interno do servidor', 'error');
    });
}

/**
 * Configurar eventos do modal de transferência
 */
function configurarEventosTransferencia() {
    // Buscar produto por SKU
    const skuInput = document.getElementById('transferenciaProduto');
    
    if (skuInput) {
        skuInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarProdutoTransferencia();
            }
        });
    }
    
    // Configurar scanner
    const scannerBtn = document.querySelector('#modalTransferencia .btn-outline-primary');
    if (scannerBtn) {
        scannerBtn.addEventListener('click', () => abrirScanner('transferencia'));
    }
    
    // Configurar envio do formulário
    const form = document.getElementById('formTransferencia');
    if (form) {
        form.addEventListener('submit', salvarTransferencia);
    }
    
    // Carregar armazenagens de destino
    carregarArmazenagensDestino();
}

/**
 * Buscar produto para transferência
 */
function buscarProdutoTransferencia() {
    const sku = document.getElementById('transferenciaProduto').value.trim();
    if (!sku) {
        Swal.fire('Erro', 'Digite o SKU do produto', 'error');
        return;
    }
    
    // Mostrar loading
    const infoProduto = document.getElementById('infoProdutoTransferencia');
    infoProduto.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando produto...</div>';
    infoProduto.style.display = 'block';
    
    // Fazer requisição AJAX
    fetch(buildUrl(`/api/produtos/buscar-por-sku?sku=${encodeURIComponent(sku)}`))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.produto) {
                exibirInfoProdutoTransferencia(data.produto);
                carregarVariacoesTransferencia(data.produto.id);
            } else {
                infoProduto.innerHTML = '<div class="alert alert-warning">Produto não encontrado</div>';
            }
        })
        .catch(error => {
            console.error('Erro ao buscar produto:', error);
            infoProduto.innerHTML = '<div class="alert alert-danger">Erro ao buscar produto</div>';
        });
}

/**
 * Exibir informações do produto para transferência
 */
function exibirInfoProdutoTransferencia(produto) {
    const infoProduto = document.getElementById('infoProdutoTransferencia');
    infoProduto.innerHTML = `
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">${produto.nome}</h6>
                <p class="card-text">
                    <strong>SKU:</strong> ${produto.SKU}<br>
                    <strong>Categoria:</strong> ${produto.categoria}<br>
                    <strong>Status:</strong> <span class="badge bg-success">Ativo</span>
                </p>
            </div>
        </div>
    `;
}

/**
 * Carregar variações para transferência
 */
function carregarVariacoesTransferencia(produtoId) {
    const variacaoSelect = document.getElementById('transferenciaVariacao');
    if (!variacaoSelect) return;
    
    // Limpar opções existentes
    variacaoSelect.innerHTML = '<option value="">Selecione a variação</option>';
    
    // Buscar variações do produto
    fetch(buildUrl(`/api/produtos/${produtoId}/variacoes`))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.variacoes) {
                data.variacoes.forEach(variacao => {
                    const option = document.createElement('option');
                    option.value = variacao.id;
                    option.textContent = `${variacao.tamanho || 'N/A'} - ${variacao.cor || 'Sem cor'} (Estoque: ${variacao.estoque || 0})`;
                    variacaoSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao carregar variações:', error);
        });
}

/**
 * Carregar armazenagens de destino
 */
function carregarArmazenagensDestino() {
    const armazenagemOrigemId = document.getElementById('transferenciaArmazenagemOrigem').value;
    const selectDestino = document.getElementById('transferenciaArmazenagemDestino');
    
    if (!selectDestino) return;
    
    // Limpar opções existentes
    selectDestino.innerHTML = '<option value="">Selecione o destino</option>';
    
    // Buscar armazenagens disponíveis
    fetch(buildUrl('/api/armazenagens/listar'))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.armazenagens) {
                data.armazenagens.forEach(armazenagem => {
                    if (armazenagem.id != armazenagemOrigemId) {
                        const option = document.createElement('option');
                        option.value = armazenagem.id;
                        option.textContent = `${armazenagem.codigo} - ${armazenagem.descricao}`;
                        selectDestino.appendChild(option);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Erro ao carregar armazenagens:', error);
        });
}

/**
 * Salvar transferência
 */
function salvarTransferencia(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('formTransferencia');
    const formData = new FormData(form);
    const dados = Object.fromEntries(formData.entries());
    
    // Validações
    if (!dados.sku || !dados.variacao_id || !dados.quantidade || !dados.armazenagem_destino_id) {
        Swal.fire('Erro', 'Preencha todos os campos obrigatórios', 'error');
        return;
    }
    
    if (parseFloat(dados.quantidade) <= 0) {
        Swal.fire('Erro', 'A quantidade deve ser maior que zero', 'error');
        return;
    }
    
    // Mostrar confirmação
    Swal.fire({
        title: 'Confirmar Transferência',
        text: `Deseja transferir ${dados.quantidade} unidades para a armazenagem de destino?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, Transferir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            executarTransferencia(dados);
        }
    });
}

/**
 * Executar transferência
 */
function executarTransferencia(dados) {
    // Mostrar loading
    Swal.fire({
        title: 'Executando transferência...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar dados
    fetch(buildUrl('/api/transferencias/criar'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso', 'Transferência realizada com sucesso', 'success');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalTransferencia'));
            modal.hide();
            
            // Recarregar dados da página
            location.reload();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao realizar transferência', 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao realizar transferência:', error);
        Swal.fire('Erro', 'Erro interno do servidor', 'error');
    });
}

/**
 * Funções utilitárias
 */

function getArmazenagemIdFromUrl() {
    const urlParts = window.location.pathname.split('/');
    // Se a URL for /armazenagens/edit/{id}, pegar o último segmento
    // Se a URL for /armazenagens/{id}, pegar o último segmento
    // Se a URL for /armazenagens/show/{id}, pegar o último segmento
    const lastSegment = urlParts[urlParts.length - 1];
    
    // Verificar se o último segmento é um número (ID)
    if (!isNaN(lastSegment) && lastSegment !== '') {
        return lastSegment;
    }
    
    // Se não for um número, tentar pegar o penúltimo segmento
    const penultimateSegment = urlParts[urlParts.length - 2];
    if (!isNaN(penultimateSegment) && penultimateSegment !== '') {
        return penultimateSegment;
    }
    
    // Fallback: tentar encontrar um número na URL
    for (let i = urlParts.length - 1; i >= 0; i--) {
        if (!isNaN(urlParts[i]) && urlParts[i] !== '') {
            return urlParts[i];
        }
    }
    
    console.error('Não foi possível extrair o ID da armazenagem da URL');
    return null;
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '-';
    const date = new Date(dateTimeString);
    return date.toLocaleString('pt-BR');
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function getStatusClass(status) {
    const statusMap = {
        'pendente': 'bg-warning',
        'em_andamento': 'bg-info',
        'concluida': 'bg-success',
        'cancelada': 'bg-danger'
    };
    return statusMap[status] || 'bg-secondary';
}

function getHistoricoColorClass(tipo) {
    const colorMap = {
        'criacao': 'bg-primary',
        'movimentacao': 'bg-success',
        'transferencia': 'bg-info',
        'ajuste': 'bg-warning',
        'erro': 'bg-danger'
    };
    return colorMap[tipo] || 'bg-secondary';
}

function getHistoricoIconClass(tipo) {
    const iconMap = {
        'criacao': 'fa-plus',
        'movimentacao': 'fa-exchange-alt',
        'transferencia': 'fa-arrows-alt',
        'ajuste': 'fa-tools',
        'erro': 'fa-exclamation-triangle'
    };
    return iconMap[tipo] || 'fa-circle';
}
