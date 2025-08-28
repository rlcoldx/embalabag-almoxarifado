/**
 * Script para criação de Movimentação Interna
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar formulário
    inicializarFormulario();
    
    // Inicializar validações
    inicializarValidacoes();
    
    // Carregar dados se ID fornecido
    const urlParams = new URLSearchParams(window.location.search);
    const itemNfId = urlParams.get('item_nf_id');
    if (itemNfId) {
        carregarDadosItem(itemNfId);
    }
});

/**
 * Inicializa o formulário de movimentação
 */
function inicializarFormulario() {
    document.getElementById('formMovimentacao').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validarFormulario()) {
            enviarFormulario();
        }
    });
    
    // Carregar armazenagens disponíveis
    carregarArmazenagens();
    
    // Buscar produtos
    document.getElementById('btnBuscarProduto').addEventListener('click', buscarProdutos);
}

/**
 * Inicializa validações específicas
 */
function inicializarValidacoes() {
    // Validar quantidade não pode ser maior que disponível
    document.getElementById('quantidade_movimentada').addEventListener('blur', function() {
        const disponivel = parseInt(document.getElementById('quantidade_disponivel').value) || 0;
        const movimentada = parseInt(this.value) || 0;
        
        if (movimentada > disponivel) {
            Swal.fire('Atenção!', 'A quantidade movimentada não pode ser maior que a disponível.', 'warning');
            this.value = disponivel;
        }
    });
    
    // Validar origem e destino não podem ser iguais
    document.getElementById('armazenagem_destino_id').addEventListener('change', function() {
        const origem = document.getElementById('armazenagem_origem_id').value;
        const destino = this.value;
        
        if (origem && destino && origem === destino) {
            Swal.fire('Atenção!', 'A armazenagem de origem e destino não podem ser iguais.', 'warning');
            this.value = '';
        }
    });
}

/**
 * Carrega armazenagens disponíveis
 */
function carregarArmazenagens() {
    fetch(DOMAIN + '/recebimento/armazenagens/disponiveis', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherSelectArmazenagens(data.armazenagens);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar armazenagens:', error);
    });
}

/**
 * Preenche os selects de armazenagem
 */
function preencherSelectArmazenagens(armazenagens) {
    const selectOrigem = document.getElementById('armazenagem_origem_id');
    const selectDestino = document.getElementById('armazenagem_destino_id');
    
    // Limpar opções existentes
    selectOrigem.innerHTML = '<option value="">Selecione a origem</option>';
    selectDestino.innerHTML = '<option value="">Selecione o destino</option>';
    
    armazenagens.forEach(arm => {
        const optionOrigem = document.createElement('option');
        optionOrigem.value = arm.id;
        optionOrigem.textContent = `${arm.codigo} - ${arm.descricao} (${arm.capacidade_disponivel}/${arm.capacidade_maxima})`;
        selectOrigem.appendChild(optionOrigem);
        
        const optionDestino = document.createElement('option');
        optionDestino.value = arm.id;
        optionDestino.textContent = `${arm.codigo} - ${arm.descricao} (${arm.capacidade_disponivel}/${arm.capacidade_maxima})`;
        selectDestino.appendChild(optionDestino);
    });
}

/**
 * Busca produtos disponíveis
 */
function buscarProdutos() {
    const termo = document.getElementById('termo_busca_produto').value.trim();
    
    if (!termo) {
        Swal.fire('Atenção!', 'Digite um termo para buscar produtos.', 'warning');
        return;
    }
    
    fetch(DOMAIN + '/recebimento/produtos/buscar?termo=' + encodeURIComponent(termo), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherTabelaProdutos(data.produtos);
        } else {
            Swal.fire('Erro!', data.message || 'Nenhum produto encontrado.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire('Erro!', 'Erro ao buscar produtos.', 'error');
    });
}

/**
 * Preenche a tabela de produtos encontrados
 */
function preencherTabelaProdutos(produtos) {
    const tbody = document.querySelector('#tabelaProdutos tbody');
    tbody.innerHTML = '';
    
    produtos.forEach(produto => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${produto.codigo_produto}</td>
            <td>${produto.descricao_produto}</td>
            <td>${produto.quantidade_disponivel}</td>
            <td>${produto.armazenagem_atual}</td>
            <td>
                <button type="button" class="btn btn-primary btn-sm" onclick="selecionarProduto(${produto.item_nf_id})">
                    <i class="fas fa-check"></i> Selecionar
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    // Mostrar tabela
    document.getElementById('tabelaProdutos').style.display = 'table';
}

/**
 * Seleciona um produto para movimentação
 */
function selecionarProduto(itemNfId) {
    carregarDadosItem(itemNfId);
    document.getElementById('formularioMovimentacao').style.display = 'block';
}

/**
 * Carrega dados do item selecionado
 */
function carregarDadosItem(itemNfId) {
    fetch(DOMAIN + '/recebimento/produtos/' + itemNfId + '/dados', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherDadosItem(data.item);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar dados do item:', error);
    });
}

/**
 * Preenche os dados do item no formulário
 */
function preencherDadosItem(item) {
    document.getElementById('item_nf_id').value = item.id;
    document.getElementById('codigo_produto').value = item.codigo_produto;
    document.getElementById('descricao_produto').value = item.descricao_produto;
    document.getElementById('quantidade_disponivel').value = item.quantidade_disponivel;
    document.getElementById('armazenagem_atual').value = item.armazenagem_atual;
    document.getElementById('quantidade_movimentada').value = item.quantidade_disponivel;
    
    // Definir armazenagem de origem
    if (item.armazenagem_atual_id) {
        document.getElementById('armazenagem_origem_id').value = item.armazenagem_atual_id;
    }
    
    // Atualizar tipo de movimentação baseado na origem
    atualizarTipoMovimentacao();
}

/**
 * Atualiza o tipo de movimentação baseado na origem
 */
function atualizarTipoMovimentacao() {
    const origem = document.getElementById('armazenagem_origem_id').value;
    const tipoSelect = document.getElementById('tipo_movimentacao');
    
    if (origem) {
        // Se tem origem, é transferência ou reposição
        tipoSelect.innerHTML = `
            <option value="transferencia">Transferência</option>
            <option value="reposicao">Reposição</option>
            <option value="ajuste">Ajuste</option>
        `;
    } else {
        // Se não tem origem, é put-away
        tipoSelect.innerHTML = `
            <option value="put_away">Put-away</option>
            <option value="ajuste">Ajuste</option>
        `;
    }
}

/**
 * Valida o formulário antes do envio
 */
function validarFormulario() {
    const itemNfId = document.getElementById('item_nf_id').value.trim();
    const armazenagemDestino = document.getElementById('armazenagem_destino_id').value;
    const quantidadeMovimentada = parseInt(document.getElementById('quantidade_movimentada').value) || 0;
    const tipoMovimentacao = document.getElementById('tipo_movimentacao').value;
    
    if (!itemNfId) {
        Swal.fire('Erro!', 'Selecione um produto para movimentar.', 'error');
        return false;
    }
    
    if (!armazenagemDestino) {
        Swal.fire('Erro!', 'Selecione a armazenagem de destino.', 'error');
        return false;
    }
    
    if (quantidadeMovimentada <= 0) {
        Swal.fire('Erro!', 'A quantidade movimentada deve ser maior que zero.', 'error');
        return false;
    }
    
    if (!tipoMovimentacao) {
        Swal.fire('Erro!', 'Selecione o tipo de movimentação.', 'error');
        return false;
    }
    
    return true;
}

/**
 * Envia o formulário
 */
function enviarFormulario() {
    const form = document.getElementById('formMovimentacao');
    const formData = new FormData(form);
    
    // Mostrar loading
    Swal.fire({
        title: 'Salvando...',
        text: 'Aguarde enquanto salvamos a movimentação.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(DOMAIN + '/recebimento/movimentacoes/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Movimentação criada com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = DOMAIN + '/recebimento/movimentacoes';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao criar movimentação.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao enviar formulário.',
            confirmButtonText: 'OK'
        });
    });
}

/**
 * Executa uma movimentação imediatamente
 */
function executarMovimentacao() {
    if (!validarFormulario()) {
        return;
    }
    
    Swal.fire({
        title: 'Confirmar execução',
        text: 'Deseja executar esta movimentação imediatamente?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, executar!',
        cancelButtonText: 'Não, apenas salvar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formMovimentacao');
            const formData = new FormData(form);
            formData.append('executar_agora', '1');
            
            enviarFormularioComExecucao(formData);
        } else {
            enviarFormulario();
        }
    });
}

/**
 * Envia formulário com execução imediata
 */
function enviarFormularioComExecucao(formData) {
    Swal.fire({
        title: 'Executando...',
        text: 'Aguarde enquanto executamos a movimentação.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(DOMAIN + '/recebimento/movimentacoes/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Executada!',
                text: 'Movimentação criada e executada com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = DOMAIN + '/recebimento/movimentacoes';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao executar movimentação.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao executar movimentação.',
            confirmButtonText: 'OK'
        });
    });
} 