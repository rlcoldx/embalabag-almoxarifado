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
            
            // Carregar dados específicos da aba (sem o sufixo -tab)
            switch(target) {
                case '#movimentacoes':
                    loadMovimentacoes();
                    break;
                case '#transferencias':
                    loadTransferencias();
                    break;
                case '#historico':
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

    
    if (!armazenagemId) {
        const tbody = document.getElementById('movimentacoesTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro: ID da armazenagem não encontrado</td></tr>';
        }
        return;
    }
    
    fetch(buildUrl(`/api/armazenagens/movimentacoes/${armazenagemId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMovimentacoes(data.movimentacoes || []);
            } else {
                const tbody = document.getElementById('movimentacoesTableBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-warning">Erro ao carregar movimentações</td></tr>';
                }
            }
        })
        .catch(error => {
            const tbody = document.getElementById('movimentacoesTableBody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar dados</td></tr>';
            }
        });
}

/**
 * Renderizar movimentações na tabela
 */
function renderMovimentacoes(movimentacoes) {
    const tbody = document.getElementById('movimentacoesTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!movimentacoes || movimentacoes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-inbox text-muted fs-48 mb-2 d-block"></i>
                    <p class="text-muted">Nenhuma movimentação encontrada</p>
                </td>
            </tr>
        `;
        return;
    }
    
    movimentacoes.forEach(mov => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDateTime(mov.data_movimentacao)}</td>
            <td><span class="text-uppercase badge ${mov.tipo === 'entrada' ? 'bg-warning-transparent' : 'bg-danger-transparent'}">${mov.tipo || 'N/A'}</span></td>
            <td>${mov.produto_descricao || 'N/A'}</td>
            <td>${mov.quantidade || 0}</td>
            <td>${mov.observacao || '-'}</td>
            <td>${mov.usuario_nome || 'N/A'}</td>
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
        const tbody = document.getElementById('transferenciasTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erro: ID da armazenagem não encontrado</td></tr>';
        }
        return;
    }
    
    fetch(buildUrl(`/api/armazenagens/transferencias/${armazenagemId}`))
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da API transferências:', data);
            if (data.success) {
                renderTransferencias(data.transferencias || []);
            } else {
                const tbody = document.getElementById('transferenciasTableBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-warning">Erro ao carregar transferências</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar transferências:', error);
            const tbody = document.getElementById('transferenciasTableBody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erro ao carregar dados</td></tr>';
            }
        });
}

/**
 * Renderizar transferências na tabela
 */
function renderTransferencias(transferencias) {
    const tbody = document.getElementById('transferenciasTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!transferencias || transferencias.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-inbox text-muted fs-48 mb-2 d-block"></i>
                    <p class="text-muted">Nenhuma transferência encontrada</p>
                </td>
            </tr>
        `;
        return;
    }
    
    transferencias.forEach(transf => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDateTime(transf.data_solicitacao)}</td>
            <td>${transf.item_descricao || 'N/A'}</td>
            <td>${transf.quantidade || 0}</td>
            <td>${transf.origem_codigo || 'N/A'}</td>
            <td>${transf.destino_codigo || 'N/A'}</td>
            <td><span class="badge ${getStatusClass(transf.status)}">${transf.status || 'pendente'}</span></td>
            <td>${transf.solicitante_nome || 'N/A'}</td>
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
        const container = document.getElementById('historicoTimeline');
        if (container) {
            container.innerHTML = '<div class="text-center text-danger py-4">Erro: ID da armazenagem não encontrado</div>';
        }
        return;
    }
    
    fetch(buildUrl(`/api/armazenagens/historico/${armazenagemId}`))
        .then(response => response.json())
        .then(data => {
            console.log('Resposta da API histórico:', data);
            if (data.success) {
                renderHistorico(data.historico || []);
            } else {
                const container = document.getElementById('historicoTimeline');
                if (container) {
                    container.innerHTML = '<div class="text-center text-warning py-4">Erro ao carregar histórico</div>';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar histórico:', error);
            const container = document.getElementById('historicoTimeline');
            if (container) {
                container.innerHTML = '<div class="text-center text-danger py-4">Erro ao carregar dados</div>';
            }
        });
}

/**
 * Renderizar histórico na timeline
 */
function renderHistorico(historico) {
    const container = document.getElementById('historicoTimeline');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!historico || historico.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-history text-muted fs-48 mb-3 d-block"></i>
                <h5 class="text-muted">Nenhum histórico encontrado</h5>
                <p class="text-muted">Esta armazenagem ainda não possui histórico de movimentações</p>
            </div>
        `;
        return;
    }
    
    historico.forEach(item => {
        const div = document.createElement('div');
        div.className = 'mb-3 pb-3 border-bottom';
        div.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0 me-3">
                    <span class="avatar avatar-sm rounded-circle ${getHistoricoColorClass(item.tipo)}">
                        <i class="fas ${getHistoricoIconClass(item.tipo)} fs-14"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.titulo || 'Movimentação'}</h6>
                    <p class="mb-1 text-muted">${item.descricao || 'Sem descrição'}</p>
                    <small class="text-muted">
                        <i class="far fa-clock me-1"></i>${formatDateTime(item.data)}
                        ${item.usuario ? ` • Por ${item.usuario}` : ''}
                    </small>
                </div>
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
    
    // Usar a função global do sistema de modais
    if (window.ModaisArmazem && window.ModaisArmazem.abrirTransferencia) {
        window.ModaisArmazem.abrirTransferencia(armazenagemId);
    } else {
        // Fallback se a função global não estiver disponível
        // Configurar modal
        document.getElementById('transferenciaArmazenagemOrigem').value = armazenagemId;
        document.getElementById('formTransferencia').reset();
        document.getElementById('infoProdutoTransferencia').style.display = 'none';
        
        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalTransferencia'));
        modal.show();
    }
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
        processData: false,
        contentType: false,
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
 * Buscar estoque disponível na armazenagem de origem
 */
function buscarEstoqueDisponivel(produtoId, variacaoId, armazenagemId) {
    fetch(buildUrl(`/api/armazenagens/estoque/${armazenagemId}?produto_id=${produtoId}&variacao_id=${variacaoId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.estoque) {
                const estoque = data.estoque.quantidade || 0;
                document.getElementById('transferenciaEstoqueAtual').value = estoque + ' unidades';
                document.getElementById('transferenciaQuantidade').max = estoque;
                
                // Atualizar validação
                if (estoque === 0) {
                    $('#validacoesTransferencia').show();
                    $('#listaValidacoes').html('<li>Não há estoque disponível desta variação nesta armazenagem</li>');
                } else {
                    $('#validacoesTransferencia').hide();
                }
            } else {
                document.getElementById('transferenciaEstoqueAtual').value = '0 unidades';
                $('#validacoesTransferencia').show();
                $('#listaValidacoes').html('<li>Produto não encontrado nesta armazenagem</li>');
            }
        })
        .catch(error => {
            console.error('Erro ao buscar estoque:', error);
            document.getElementById('transferenciaEstoqueAtual').value = '0 unidades';
        });
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
    
    // Habilitar o select
    variacaoSelect.disabled = false;
    
    // Limpar opções existentes
    variacaoSelect.innerHTML = '<option value="">Carregando variações...</option>';
    
    // Buscar variações do produto
    fetch(buildUrl(`/api/produtos/variacoes/${produtoId}`))
        .then(response => response.json())
        .then(data => {
            variacaoSelect.innerHTML = '<option value="">Selecione a variação</option>';
            
            if (data.success && data.variacoes && data.variacoes.length > 0) {
                data.variacoes.forEach(variacao => {
                    const option = document.createElement('option');
                    option.value = variacao.id;
                    option.setAttribute('data-id-produto', produtoId);
                    option.textContent = `${variacao.tamanho || 'N/A'} - ${variacao.cor || 'Sem cor'} (Estoque Total: ${variacao.estoque || 0})`;
                    variacaoSelect.appendChild(option);
                });
            } else {
                variacaoSelect.innerHTML = '<option value="">Nenhuma variação encontrada</option>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar variações:', error);
            variacaoSelect.innerHTML = '<option value="">Erro ao carregar variações</option>';
        });
}


/**
 * Salvar transferência
 */
function salvarTransferencia(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('formTransferencia');
    const formData = new FormData(form);
    
    // Pegar os valores corretos
    const variacaoSelect = document.querySelector('#transferenciaVariacao');
    const selectedVariacaoOption = variacaoSelect.options[variacaoSelect.selectedIndex];
    const idProduto = selectedVariacaoOption ? selectedVariacaoOption.getAttribute('data-id-produto') : formData.get('id_produto');
    
    const dados = {
        armazenagem_origem_id: formData.get('armazenagem_origem_id'),
        armazenagem_destino_id: formData.get('armazenagem_destino_id'),
        id_produto: idProduto,
        variacao_id: formData.get('variacao_id'),
        quantidade: parseInt(formData.get('quantidade')),
        motivo: formData.get('motivo') || 'outro',
        observacoes: formData.get('observacoes') || ''
    };
    
    // Validações
    if (!dados.id_produto || !dados.variacao_id || !dados.quantidade || !dados.armazenagem_destino_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Obrigatórios',
            text: 'Por favor, preencha todos os campos obrigatórios.'
        });
        return;
    }
    
    if (dados.quantidade <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Quantidade Inválida',
            text: 'A quantidade deve ser maior que zero.'
        });
        return;
    }
    
    // Validar se quantidade não excede estoque
    const estoqueAtual = parseInt(document.getElementById('transferenciaEstoqueAtual').value) || 0;
    if (dados.quantidade > estoqueAtual) {
        Swal.fire({
            icon: 'warning',
            title: 'Quantidade Inválida',
            text: 'Quantidade não pode exceder o estoque disponível.'
        });
        return;
    }
    
    // Validar se há espaço suficiente no destino
    const selectDestino = document.querySelector('#transferenciaArmazenagemDestino');
    const selectedDestinoOption = selectDestino.options[selectDestino.selectedIndex];
    if (selectedDestinoOption) {
        const espacoDisponivel = parseInt(selectedDestinoOption.getAttribute('data-espaco-disponivel')) || 0;
        if (dados.quantidade > espacoDisponivel) {
            Swal.fire({
                icon: 'warning',
                title: 'Espaço Insuficiente',
                text: `A quantidade (${dados.quantidade}) excede o espaço disponível (${espacoDisponivel}) no armazém de destino.`
            });
            return;
        }
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
