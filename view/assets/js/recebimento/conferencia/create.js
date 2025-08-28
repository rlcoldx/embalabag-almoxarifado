/**
 * Script para criação de Conferência
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar formulário
    inicializarFormulario();
    
    // Inicializar validações
    inicializarValidacoes();
    
    // Carregar itens da NF se ID fornecido
    const urlParams = new URLSearchParams(window.location.search);
    const itemNfId = urlParams.get('item_nf_id');
    if (itemNfId) {
        carregarDadosItem(itemNfId);
    }
});

/**
 * Inicializa o formulário de conferência
 */
function inicializarFormulario() {
    document.getElementById('formConferencia').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validarFormulario()) {
            enviarFormulario();
        }
    });
    
    // Calcular diferenças automaticamente
    document.getElementById('quantidade_recebida').addEventListener('input', calcularDiferencas);
    document.getElementById('quantidade_avariada').addEventListener('input', calcularDiferencas);
    document.getElementById('quantidade_devolvida').addEventListener('input', calcularDiferencas);
}

/**
 * Inicializa validações específicas
 */
function inicializarValidacoes() {
    // Validar quantidade recebida não pode ser maior que esperada
    document.getElementById('quantidade_recebida').addEventListener('blur', function() {
        const esperada = parseInt(document.getElementById('quantidade_esperada').value) || 0;
        const recebida = parseInt(this.value) || 0;
        
        if (recebida > esperada) {
            Swal.fire('Atenção!', 'A quantidade recebida não pode ser maior que a esperada.', 'warning');
            this.value = esperada;
            calcularDiferencas();
        }
    });
    
    // Validar soma de avariada + devolvida não pode ser maior que recebida
    document.getElementById('quantidade_avariada').addEventListener('blur', validarSomaQuantidades);
    document.getElementById('quantidade_devolvida').addEventListener('blur', validarSomaQuantidades);
}

/**
 * Carrega dados do item da NF
 */
function carregarDadosItem(itemNfId) {
    fetch(DOMAIN + '/recebimento/notas-fiscais/item/' + itemNfId, {
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
    document.getElementById('quantidade_esperada').value = item.quantidade;
    document.getElementById('unidade').value = item.unidade;
    document.getElementById('valor_unitario').value = item.valor_unitario;
    
    // Preencher dados da NF
    document.getElementById('numero_nf').value = item.numero_nf;
    document.getElementById('fornecedor').value = item.fornecedor;
    
    // Definir quantidade recebida igual à esperada por padrão
    document.getElementById('quantidade_recebida').value = item.quantidade;
    
    calcularDiferencas();
}

/**
 * Calcula as diferenças entre quantidades
 */
function calcularDiferencas() {
    const esperada = parseInt(document.getElementById('quantidade_esperada').value) || 0;
    const recebida = parseInt(document.getElementById('quantidade_recebida').value) || 0;
    const avariada = parseInt(document.getElementById('quantidade_avariada').value) || 0;
    const devolvida = parseInt(document.getElementById('quantidade_devolvida').value) || 0;
    
    // Diferença entre esperada e recebida
    const diferenca = esperada - recebida;
    document.getElementById('diferenca_quantidade').textContent = diferenca;
    
    // Quantidade em conformidade (recebida - avariada - devolvida)
    const conformidade = recebida - avariada - devolvida;
    document.getElementById('quantidade_conformidade').textContent = conformidade;
    
    // Atualizar cores dos badges
    const diferencaElement = document.getElementById('diferenca_quantidade');
    const conformidadeElement = document.getElementById('quantidade_conformidade');
    
    if (diferenca === 0) {
        diferencaElement.className = 'badge bg-success';
    } else if (diferenca > 0) {
        diferencaElement.className = 'badge bg-warning';
    } else {
        diferencaElement.className = 'badge bg-danger';
    }
    
    if (conformidade === recebida) {
        conformidadeElement.className = 'badge bg-success';
    } else if (conformidade > 0) {
        conformidadeElement.className = 'badge bg-warning';
    } else {
        conformidadeElement.className = 'badge bg-danger';
    }
}

/**
 * Valida se a soma das quantidades não excede o recebido
 */
function validarSomaQuantidades() {
    const recebida = parseInt(document.getElementById('quantidade_recebida').value) || 0;
    const avariada = parseInt(document.getElementById('quantidade_avariada').value) || 0;
    const devolvida = parseInt(document.getElementById('quantidade_devolvida').value) || 0;
    
    if (avariada + devolvida > recebida) {
        Swal.fire('Atenção!', 'A soma de avariada + devolvida não pode ser maior que a quantidade recebida.', 'warning');
        
        // Ajustar automaticamente
        if (avariada > recebida) {
            document.getElementById('quantidade_avariada').value = recebida;
            document.getElementById('quantidade_devolvida').value = 0;
        } else {
            document.getElementById('quantidade_devolvida').value = recebida - avariada;
        }
    }
    
    calcularDiferencas();
}

/**
 * Valida o formulário antes do envio
 */
function validarFormulario() {
    const itemNfId = document.getElementById('item_nf_id').value.trim();
    const quantidadeRecebida = parseInt(document.getElementById('quantidade_recebida').value) || 0;
    const statusQualidade = document.getElementById('status_qualidade').value;
    const statusIntegridade = document.getElementById('status_integridade').value;
    
    if (!itemNfId) {
        Swal.fire('Erro!', 'Selecione um item da nota fiscal.', 'error');
        return false;
    }
    
    if (quantidadeRecebida <= 0) {
        Swal.fire('Erro!', 'A quantidade recebida deve ser maior que zero.', 'error');
        return false;
    }
    
    if (!statusQualidade) {
        Swal.fire('Erro!', 'Selecione o status de qualidade.', 'error');
        return false;
    }
    
    if (!statusIntegridade) {
        Swal.fire('Erro!', 'Selecione o status de integridade.', 'error');
        return false;
    }
    
    return true;
}

/**
 * Envia o formulário
 */
function enviarFormulario() {
    const form = document.getElementById('formConferencia');
    const formData = new FormData(form);
    
    // Mostrar loading
    Swal.fire({
        title: 'Salvando...',
        text: 'Aguarde enquanto salvamos a conferência.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(DOMAIN + '/recebimento/conferencia/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Conferência realizada com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = DOMAIN + '/recebimento/conferencia';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao realizar conferência.',
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
 * Busca itens de uma nota fiscal
 */
function buscarItensNF() {
    const numeroNF = document.getElementById('numero_nf_busca').value.trim();
    
    if (!numeroNF) {
        Swal.fire('Atenção!', 'Digite o número da nota fiscal.', 'warning');
        return;
    }
    
    fetch(DOMAIN + '/recebimento/notas-fiscais/' + numeroNF + '/itens', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherSelectItens(data.itens);
        } else {
            Swal.fire('Erro!', data.message || 'Nota fiscal não encontrada.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire('Erro!', 'Erro ao buscar itens da nota fiscal.', 'error');
    });
}

/**
 * Preenche o select com os itens da NF
 */
function preencherSelectItens(itens) {
    const select = document.getElementById('item_nf_id');
    select.innerHTML = '<option value="">Selecione um item</option>';
    
    itens.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.codigo_produto} - ${item.descricao_produto} (${item.quantidade} ${item.unidade})`;
        select.appendChild(option);
    });
    
    // Mostrar seção de seleção de item
    document.getElementById('selecaoItem').style.display = 'block';
}

/**
 * Seleciona um item e carrega seus dados
 */
function selecionarItem() {
    const itemNfId = document.getElementById('item_nf_id').value;
    if (itemNfId) {
        carregarDadosItem(itemNfId);
        document.getElementById('formularioConferencia').style.display = 'block';
    }
} 