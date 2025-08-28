/**
 * Script para edição de Nota Fiscal
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar formulário
    inicializarFormulario();
    
    // Inicializar máscaras e validações
    inicializarMascaras();
    
    // Carregar dados da NF
    carregarDadosNotaFiscal();
});

/**
 * Inicializa o formulário de edição
 */
function inicializarFormulario() {
    document.getElementById('formNotaFiscal').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validarFormulario()) {
            enviarFormulario();
        }
    });
    
    // Adicionar item dinamicamente
    document.getElementById('btnAdicionarItem').addEventListener('click', function() {
        adicionarItem();
    });
}

/**
 * Inicializa máscaras nos campos
 */
function inicializarMascaras() {
    // Máscara para CNPJ
    const cnpjInput = document.getElementById('cnpj_fornecedor');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value;
        });
    }
    
    // Máscara para valor
    const valorInputs = document.querySelectorAll('.valor-item');
    valorInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseFloat(value) / 100).toFixed(2);
            e.target.value = value;
        });
    });
}

/**
 * Carrega os dados da nota fiscal
 */
function carregarDadosNotaFiscal() {
    // Obter ID da NF da URL
    const urlParts = window.location.pathname.split('/');
    const nfId = urlParts[urlParts.length - 2]; // /edit está no final
    
    fetch(DOMAIN + '/recebimento/notas-fiscais/' + nfId + '/dados', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherDadosNotaFiscal(data.nota_fiscal);
            preencherItensNotaFiscal(data.itens);
        } else {
            Swal.fire('Erro!', data.message || 'Erro ao carregar dados da nota fiscal.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao carregar dados:', error);
        Swal.fire('Erro!', 'Erro ao carregar dados da nota fiscal.', 'error');
    });
}

/**
 * Preenche os dados da nota fiscal no formulário
 */
function preencherDadosNotaFiscal(notaFiscal) {
    document.getElementById('numero').value = notaFiscal.numero;
    document.getElementById('fornecedor').value = notaFiscal.fornecedor;
    document.getElementById('cnpj_fornecedor').value = notaFiscal.cnpj_fornecedor;
    document.getElementById('data_emissao').value = notaFiscal.data_emissao;
    document.getElementById('data_vencimento').value = notaFiscal.data_vencimento;
    document.getElementById('valor_total').value = notaFiscal.valor_total;
    document.getElementById('status').value = notaFiscal.status;
    document.getElementById('observacoes').value = notaFiscal.observacoes || '';
    
    // Preencher pedido se existir
    if (notaFiscal.pedido_id) {
        document.getElementById('pedido_id').value = notaFiscal.pedido_id;
    }
}

/**
 * Preenche os itens da nota fiscal
 */
function preencherItensNotaFiscal(itens) {
    const tbody = document.querySelector('#tabelaItens tbody');
    tbody.innerHTML = '';
    
    itens.forEach((item, index) => {
        adicionarItemComDados(item, index);
    });
    
    calcularTotal();
}

/**
 * Adiciona um novo item ao formulário
 */
function adicionarItem(dados = null, index = null) {
    const tbody = document.querySelector('#tabelaItens tbody');
    const itemIndex = index !== null ? index : tbody.children.length;
    
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <input type="text" name="itens[${itemIndex}][codigo_produto]" class="form-control" 
                   value="${dados ? dados.codigo_produto : ''}" required>
        </td>
        <td>
            <input type="text" name="itens[${itemIndex}][descricao_produto]" class="form-control" 
                   value="${dados ? dados.descricao_produto : ''}" required>
        </td>
        <td>
            <input type="number" name="itens[${itemIndex}][quantidade]" class="form-control quantidade-item" 
                   value="${dados ? dados.quantidade : ''}" min="1" required>
        </td>
        <td>
            <input type="text" name="itens[${itemIndex}][unidade]" class="form-control" 
                   value="${dados ? dados.unidade : 'UN'}" required>
        </td>
        <td>
            <input type="text" name="itens[${itemIndex}][valor_unitario]" class="form-control valor-item" 
                   value="${dados ? dados.valor_unitario : ''}" required>
        </td>
        <td>
            <input type="text" name="itens[${itemIndex}][valor_total]" class="form-control valor-total-item" 
                   value="${dados ? dados.valor_total : ''}" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removerItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(tr);
    
    // Adicionar eventos aos novos campos
    const quantidadeInput = tr.querySelector('.quantidade-item');
    const valorUnitarioInput = tr.querySelector('.valor-item');
    
    quantidadeInput.addEventListener('input', function() {
        calcularValorItem(tr);
    });
    
    valorUnitarioInput.addEventListener('input', function() {
        calcularValorItem(tr);
    });
}

/**
 * Adiciona item com dados pré-preenchidos
 */
function adicionarItemComDados(dados, index) {
    adicionarItem(dados, index);
}

/**
 * Remove um item do formulário
 */
function removerItem(button) {
    const tr = button.closest('tr');
    tr.remove();
    calcularTotal();
    reindexarItens();
}

/**
 * Calcula o valor total de um item
 */
function calcularValorItem(tr) {
    const quantidade = parseFloat(tr.querySelector('.quantidade-item').value) || 0;
    const valorUnitario = parseFloat(tr.querySelector('.valor-item').value) || 0;
    const valorTotal = quantidade * valorUnitario;
    
    tr.querySelector('.valor-total-item').value = valorTotal.toFixed(2);
    calcularTotal();
}

/**
 * Calcula o total geral
 */
function calcularTotal() {
    const valorTotalInputs = document.querySelectorAll('.valor-total-item');
    let total = 0;
    
    valorTotalInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('valor_total').value = total.toFixed(2);
}

/**
 * Reindexa os itens após remoção
 */
function reindexarItens() {
    const rows = document.querySelectorAll('#tabelaItens tbody tr');
    rows.forEach((row, index) => {
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const name = input.name;
            if (name.includes('[itens[')) {
                input.name = name.replace(/itens\[\d+\]/, `itens[${index}]`);
            }
        });
    });
}

/**
 * Valida o formulário antes do envio
 */
function validarFormulario() {
    const numero = document.getElementById('numero').value.trim();
    const fornecedor = document.getElementById('fornecedor').value.trim();
    const cnpj = document.getElementById('cnpj_fornecedor').value.trim();
    
    if (!numero) {
        Swal.fire('Erro!', 'O número da nota fiscal é obrigatório.', 'error');
        return false;
    }
    
    if (!fornecedor) {
        Swal.fire('Erro!', 'O fornecedor é obrigatório.', 'error');
        return false;
    }
    
    if (!cnpj) {
        Swal.fire('Erro!', 'O CNPJ do fornecedor é obrigatório.', 'error');
        return false;
    }
    
    // Validar se há pelo menos um item
    const itens = document.querySelectorAll('#tabelaItens tbody tr');
    if (itens.length === 0) {
        Swal.fire('Erro!', 'Adicione pelo menos um item à nota fiscal.', 'error');
        return false;
    }
    
    return true;
}

/**
 * Envia o formulário
 */
function enviarFormulario() {
    const form = document.getElementById('formNotaFiscal');
    const formData = new FormData(form);
    
    // Obter ID da NF da URL
    const urlParts = window.location.pathname.split('/');
    const nfId = urlParts[urlParts.length - 2];
    
    // Mostrar loading
    Swal.fire({
        title: 'Salvando...',
        text: 'Aguarde enquanto salvamos a nota fiscal.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(DOMAIN + '/recebimento/notas-fiscais/' + nfId + '/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Nota fiscal atualizada com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = DOMAIN + '/recebimento/notas-fiscais';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao atualizar nota fiscal.',
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
 * Vincula a NF a um pedido
 */
function vincularPedido() {
    const pedidoId = document.getElementById('pedido_id').value;
    
    if (!pedidoId) {
        Swal.fire('Atenção!', 'Selecione um pedido para vincular.', 'warning');
        return;
    }
    
    // Obter ID da NF da URL
    const urlParts = window.location.pathname.split('/');
    const nfId = urlParts[urlParts.length - 2];
    
    const formData = new FormData();
    formData.append('pedido_id', pedidoId);
    
    fetch(DOMAIN + '/recebimento/notas-fiscais/' + nfId + '/vincular-pedido', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Vinculado!',
                text: 'Nota fiscal vinculada ao pedido com sucesso!',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao vincular nota fiscal.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao vincular nota fiscal.',
            confirmButtonText: 'OK'
        });
    });
} 