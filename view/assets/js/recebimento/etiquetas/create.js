/**
 * Script para criação de Etiquetas Internas
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar formulário
    inicializarFormulario();
    
    // Inicializar validações
    inicializarValidacoes();
    
    // Gerar código automático
    gerarCodigoAutomatico();
});

/**
 * Inicializa o formulário de etiqueta
 */
function inicializarFormulario() {
    document.getElementById('formEtiqueta').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validarFormulario()) {
            enviarFormulario();
        }
    });
    
    // Gerar código automático
    document.getElementById('btnGerarCodigo').addEventListener('click', gerarCodigoAutomatico);
    
    // Gerar QR Code
    document.getElementById('btnGerarQRCode').addEventListener('click', gerarQRCode);
    
    // Atualizar conteúdo baseado no tipo
    document.getElementById('tipo_etiqueta').addEventListener('change', atualizarConteudo);
}

/**
 * Inicializa validações específicas
 */
function inicializarValidacoes() {
    // Validar código único
    document.getElementById('codigo').addEventListener('blur', function() {
        const codigo = this.value.trim();
        if (codigo) {
            verificarCodigoUnico(codigo);
        }
    });
}

/**
 * Gera código automático para a etiqueta
 */
function gerarCodigoAutomatico() {
    const tipo = document.getElementById('tipo_etiqueta').value;
    const prefixo = getPrefixoPorTipo(tipo);
    const timestamp = Date.now().toString().slice(-6);
    const codigo = `${prefixo}${timestamp}`;
    
    document.getElementById('codigo').value = codigo;
    verificarCodigoUnico(codigo);
}

/**
 * Retorna o prefixo baseado no tipo de etiqueta
 */
function getPrefixoPorTipo(tipo) {
    switch (tipo) {
        case 'localizacao':
            return 'LOC';
        case 'palete':
            return 'PAL';
        case 'caixa':
            return 'CAI';
        case 'produto':
            return 'PRO';
        case 'armazenagem':
            return 'ARM';
        default:
            return 'ETQ';
    }
}

/**
 * Verifica se o código é único
 */
function verificarCodigoUnico(codigo) {
    fetch(DOMAIN + '/recebimento/etiquetas/verificar-codigo?codigo=' + encodeURIComponent(codigo), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const codigoInput = document.getElementById('codigo');
        const feedback = document.getElementById('codigo-feedback');
        
        if (data.disponivel) {
            codigoInput.classList.remove('is-invalid');
            codigoInput.classList.add('is-valid');
            feedback.textContent = 'Código disponível';
            feedback.className = 'valid-feedback';
        } else {
            codigoInput.classList.remove('is-valid');
            codigoInput.classList.add('is-invalid');
            feedback.textContent = 'Código já existe';
            feedback.className = 'invalid-feedback';
        }
    })
    .catch(error => {
        console.error('Erro ao verificar código:', error);
    });
}

/**
 * Atualiza o conteúdo baseado no tipo de etiqueta
 */
function atualizarConteudo() {
    const tipo = document.getElementById('tipo_etiqueta').value;
    const conteudoTextarea = document.getElementById('conteudo');
    
    // Limpar conteúdo atual
    conteudoTextarea.value = '';
    
    // Gerar novo código
    gerarCodigoAutomatico();
    
    // Sugerir conteúdo baseado no tipo
    const sugestao = getSugestaoConteudo(tipo);
    if (sugestao) {
        conteudoTextarea.placeholder = sugestao;
    }
}

/**
 * Retorna sugestão de conteúdo baseada no tipo
 */
function getSugestaoConteudo(tipo) {
    switch (tipo) {
        case 'localizacao':
            return 'Ex: Setor A - Prateleira 01 - Posição 05';
        case 'palete':
            return 'Ex: Palete de Produtos Diversos - Capacidade: 1000kg';
        case 'caixa':
            return 'Ex: Caixa de Produtos - Conteúdo: 50 unidades';
        case 'produto':
            return 'Ex: Produto XYZ - Código: 12345 - Quantidade: 100';
        case 'armazenagem':
            return 'Ex: Área de Armazenamento - Tipo: Refrigerado';
        default:
            return 'Digite o conteúdo da etiqueta...';
    }
}

/**
 * Gera QR Code para a etiqueta
 */
function gerarQRCode() {
    const codigo = document.getElementById('codigo').value.trim();
    const conteudo = document.getElementById('conteudo').value.trim();
    
    if (!codigo) {
        Swal.fire('Atenção!', 'Digite um código para gerar o QR Code.', 'warning');
        return;
    }
    
    if (!conteudo) {
        Swal.fire('Atenção!', 'Digite o conteúdo para gerar o QR Code.', 'warning');
        return;
    }
    
    // Dados para o QR Code
    const dados = {
        codigo: codigo,
        tipo: document.getElementById('tipo_etiqueta').value,
        conteudo: conteudo,
        timestamp: new Date().toISOString()
    };
    
    // Gerar QR Code usando biblioteca (exemplo com qrcode.js)
    const qrContainer = document.getElementById('qr-code-container');
    qrContainer.innerHTML = '';
    
    // Aqui você pode usar uma biblioteca como qrcode.js
    // Por enquanto, vamos apenas mostrar os dados
    const qrData = JSON.stringify(dados);
    document.getElementById('qr_code_data').value = qrData;
    
    // Mostrar preview do QR Code
    mostrarPreviewQRCode(qrData);
}

/**
 * Mostra preview do QR Code
 */
function mostrarPreviewQRCode(dados) {
    // Aqui você pode integrar com uma biblioteca de QR Code
    // Por exemplo: new QRCode(document.getElementById("qr-code-container"), dados);
    
    const container = document.getElementById('qr-code-container');
    container.innerHTML = `
        <div class="alert alert-info">
            <h6>Preview do QR Code:</h6>
            <small class="text-muted">${dados}</small>
        </div>
    `;
    
    // Mostrar seção de QR Code
    document.getElementById('secao-qr-code').style.display = 'block';
}

/**
 * Valida o formulário antes do envio
 */
function validarFormulario() {
    const codigo = document.getElementById('codigo').value.trim();
    const tipoEtiqueta = document.getElementById('tipo_etiqueta').value;
    const conteudo = document.getElementById('conteudo').value.trim();
    
    if (!codigo) {
        Swal.fire('Erro!', 'O código da etiqueta é obrigatório.', 'error');
        return false;
    }
    
    if (!tipoEtiqueta) {
        Swal.fire('Erro!', 'Selecione o tipo de etiqueta.', 'error');
        return false;
    }
    
    if (!conteudo) {
        Swal.fire('Erro!', 'O conteúdo da etiqueta é obrigatório.', 'error');
        return false;
    }
    
    // Verificar se código é válido
    const codigoInput = document.getElementById('codigo');
    if (codigoInput.classList.contains('is-invalid')) {
        Swal.fire('Erro!', 'O código da etiqueta já existe. Escolha outro código.', 'error');
        return false;
    }
    
    return true;
}

/**
 * Envia o formulário
 */
function enviarFormulario() {
    const form = document.getElementById('formEtiqueta');
    const formData = new FormData(form);
    
    // Mostrar loading
    Swal.fire({
        title: 'Salvando...',
        text: 'Aguarde enquanto salvamos a etiqueta.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(DOMAIN + '/recebimento/etiquetas/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Etiqueta criada com sucesso!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = DOMAIN + '/recebimento/etiquetas';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao criar etiqueta.',
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
 * Imprime a etiqueta
 */
function imprimirEtiqueta() {
    if (!validarFormulario()) {
        return;
    }
    
    Swal.fire({
        title: 'Confirmar impressão',
        text: 'Deseja imprimir esta etiqueta após salvar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, imprimir!',
        cancelButtonText: 'Não, apenas salvar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formEtiqueta');
            const formData = new FormData(form);
            formData.append('imprimir_agora', '1');
            
            enviarFormularioComImpressao(formData);
        } else {
            enviarFormulario();
        }
    });
}

/**
 * Envia formulário com impressão
 */
function enviarFormularioComImpressao(formData) {
    Swal.fire({
        title: 'Salvando e imprimindo...',
        text: 'Aguarde enquanto salvamos e imprimimos a etiqueta.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(DOMAIN + '/recebimento/etiquetas/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Impressa!',
                text: 'Etiqueta criada e enviada para impressão!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = DOMAIN + '/recebimento/etiquetas';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao imprimir etiqueta.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao imprimir etiqueta.',
            confirmButtonText: 'OK'
        });
    });
}

/**
 * Visualiza preview da etiqueta
 */
function visualizarPreview() {
    const codigo = document.getElementById('codigo').value.trim();
    const tipo = document.getElementById('tipo_etiqueta').value;
    const conteudo = document.getElementById('conteudo').value.trim();
    
    if (!codigo || !conteudo) {
        Swal.fire('Atenção!', 'Preencha o código e conteúdo para visualizar o preview.', 'warning');
        return;
    }
    
    // Abrir modal com preview
    const modal = document.getElementById('previewModal');
    const modalBody = modal.querySelector('.modal-body');
    
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="border p-3 mb-3" style="max-width: 300px; margin: 0 auto;">
                <h6 class="mb-2">${codigo}</h6>
                <p class="mb-2">${conteudo}</p>
                <small class="text-muted">Tipo: ${tipo}</small>
            </div>
            <div id="qr-preview" class="mb-3"></div>
        </div>
    `;
    
    // Gerar QR Code no preview
    const qrData = JSON.stringify({ codigo, tipo, conteudo });
    // Aqui você pode usar uma biblioteca de QR Code para gerar o preview
    
    // Mostrar modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
} 