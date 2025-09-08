/**
 * Script para gerenciamento de produtos com estoque baixo
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar botão de exportar
    inicializarBotaoExportar();
    
    // Inicializar botão de salvar compra
    inicializarBotaoSalvarCompra();
});

/**
 * Abre o modal de compra com as informações do produto
 * @param {number} produtoId - ID do produto a ser comprado
 */
function abrirModalCompra(produtoId) {
    // Buscar informações do produto
    fetch(buildUrl(`/produtos/buscar/${produtoId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const produto = data.produto;
                document.getElementById('produto_id').value = produto.id;
                
                // Calcular quantidade recomendada para compra (estoque_minimo - estoque_atual) + alguma margem
                const quantidadeRecomendada = Math.max(1, (produto.estoque_minimo - produto.estoque_atual) * 2);
                document.getElementById('quantidade').value = quantidadeRecomendada;
                
                // Exibir informações do produto
                document.getElementById('modal-produto-info').innerHTML = `
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <span class="avatar avatar-md rounded-circle bg-light me-2">
                            ${produto.imagem ? `<img src="${PATH}/${produto.imagem}" alt="${produto.nome}">` : `<i class="fas fa-box fs-24"></i>`}
                        </span>
                    </div>
                    <h5 class="mb-1">${produto.nome}</h5>
                    <p class="text-muted mb-1">Código: ${produto.codigo}</p>
                    <div class="d-flex justify-content-center gap-3 mb-2">
                        <div>
                            <span class="text-muted d-block fs-12">Estoque Atual</span>
                            <span class="badge bg-danger-transparent">${produto.estoque_atual}</span>
                        </div>
                        <div>
                            <span class="text-muted d-block fs-12">Estoque Mínimo</span>
                            <span class="badge bg-info-transparent">${produto.estoque_minimo}</span>
                        </div>
                    </div>
                `;
                
                // Abrir o modal
                const modalCompra = new bootstrap.Modal(document.getElementById('modal-compra'));
                modalCompra.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao buscar informações do produto.',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Ocorreu um erro ao processar a solicitação.',
                confirmButtonText: 'OK'
            });
        });
}

/**
 * Inicializa o botão de salvar compra
 */
function inicializarBotaoSalvarCompra() {
    document.getElementById('btn-salvar-compra').addEventListener('click', function() {
        const form = document.getElementById('form-compra');
        
        // Validar formulário
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        
        // Enviar dados
        fetch(buildUrl('/produtos/entrada-estoque'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Estoque atualizado com sucesso.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Fechar o modal e recarregar a página
                    bootstrap.Modal.getInstance(document.getElementById('modal-compra')).hide();
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Ocorreu um erro ao processar a solicitação.',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Ocorreu um erro ao processar a solicitação.',
                confirmButtonText: 'OK'
            });
        });
    });
}

/**
 * Inicializa o botão de exportar
 */
function inicializarBotaoExportar() {
    document.getElementById('btn-export').addEventListener('click', function() {
        window.location.href = buildUrl('/produtos/exportar-estoque-baixo');
    });
}