/**
 * Script para criar notas fiscais
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    let produtos = [];
    let produtosList = [];
    
    // Inicializar componentes
    initProdutos();
    initEventListeners();
});

/**
 * Carrega a lista de produtos e inicializa o select
 */
function initProdutos() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Carregar lista de produtos
    fetch(buildUrl('/produtos/listar'))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                produtosList = data.produtos || [];
                const select = document.getElementById('produto_id');
                
                produtosList.forEach(produto => {
                    const option = document.createElement('option');
                    option.value = produto.id;
                    option.textContent = `${produto.codigo} - ${produto.nome}`;
                    option.dataset.preco = produto.preco_venda || 0;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produtos:', error);
        });
}

/**
 * Inicializa todos os event listeners da página
 */
function initEventListeners() {
    // Quando selecionar um produto, preencher o valor unitário
    document.getElementById('produto_id').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.dataset.preco) {
            document.getElementById('valor_unitario').value = parseFloat(option.dataset.preco).toFixed(2);
        } else {
            document.getElementById('valor_unitario').value = '';
        }
    });
    
    // Abrir modal para adicionar produto
    document.getElementById('btn-add-produto').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modal-add-produto'));
        modal.show();
    });
    
    // Adicionar produto à tabela
    document.getElementById('btn-confirmar-produto').addEventListener('click', adicionarProduto);
    
    // Submeter o formulário
    document.getElementById('formNotaFiscal').addEventListener('submit', submitForm);
    
    // Botão cancelar
    document.getElementById('btn-cancelar').addEventListener('click', cancelarForm);
}

/**
 * Adiciona um produto à lista de produtos da nota fiscal
 */
function adicionarProduto() {
    const produtoSelect = document.getElementById('produto_id');
    const produtoId = produtoSelect.value;
    const quantidade = document.getElementById('quantidade').value;
    const valorUnitario = document.getElementById('valor_unitario').value;
    
    if (!produtoId || !quantidade || !valorUnitario) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, preencha todos os campos do produto.'
        });
        return;
    }
    
    const selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
    const produtoNome = selectedOption.textContent;
    const subtotal = (parseFloat(quantidade) * parseFloat(valorUnitario)).toFixed(2);
    
    // Adicionar à lista de produtos
    const produto = {
        id: produtoId,
        nome: produtoNome,
        quantidade: quantidade,
        valor_unitario: valorUnitario,
        subtotal: subtotal
    };
    
    produtos.push(produto);
    atualizarTabelaProdutos();
    
    // Fechar o modal
    bootstrap.Modal.getInstance(document.getElementById('modal-add-produto')).hide();
    
    // Limpar campos
    produtoSelect.value = '';
    document.getElementById('quantidade').value = '1';
    document.getElementById('valor_unitario').value = '';
}

/**
 * Atualiza a tabela de produtos da nota fiscal
 */
function atualizarTabelaProdutos() {
    const tbody = document.getElementById('tbody-produtos');
    const totalElement = document.getElementById('total-nota');
    
    // Limpar a tabela
    tbody.innerHTML = '';
    
    if (produtos.length === 0) {
        tbody.innerHTML = `
            <tr class="empty-row">
                <td colspan="5" class="text-center py-4">
                    <i class="fas fa-shopping-basket fs-24 text-muted mb-1 d-block"></i>
                    <p class="text-muted mb-0">Nenhum produto adicionado</p>
                </td>
            </tr>
        `;
        totalElement.textContent = 'R$ 0,00';
        return;
    }
    
    // Calcular total
    let total = 0;
    
    // Adicionar produtos à tabela
    produtos.forEach((produto, index) => {
        total += parseFloat(produto.subtotal);
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${produto.nome}</td>
            <td>${produto.quantidade}</td>
            <td>R$ ${parseFloat(produto.valor_unitario).toFixed(2)}</td>
            <td>R$ ${parseFloat(produto.subtotal).toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger-light btn-icon" data-index="${index}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        // Adicionar evento para remover produto
        const btnRemover = tr.querySelector('.btn-danger-light');
        btnRemover.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            produtos.splice(index, 1);
            atualizarTabelaProdutos();
        });
        
        tbody.appendChild(tr);
    });
    
    // Atualizar total
    totalElement.textContent = `R$ ${total.toFixed(2)}`;
}

/**
 * Submete o formulário de nota fiscal
 * @param {Event} e - Evento de submissão do formulário
 */
function submitForm(e) {
    e.preventDefault();
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    if (produtos.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Adicione pelo menos um produto à nota fiscal.'
        });
        return;
    }
    
    const formData = new FormData(this);
    
    // Adicionar produtos ao FormData
    formData.append('produtos', JSON.stringify(produtos));
    
    // Enviar dados para o servidor
    fetch(buildUrl('/recebimento/store'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: 'Nota fiscal registrada com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = buildUrl('/recebimento');
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: `Erro ao registrar nota fiscal: ${data.message || 'Tente novamente mais tarde.'}`,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Erro ao enviar dados:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Ocorreu um erro ao processar sua solicitação.',
            confirmButtonText: 'OK'
        });
    });
}

/**
 * Cancela o formulário de nota fiscal
 */
function cancelarForm() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Deseja cancelar o registro desta nota fiscal? Todos os dados serão perdidos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, cancelar',
        cancelButtonText: 'Não, continuar editando'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = buildUrl('/recebimento');
        }
    });
}