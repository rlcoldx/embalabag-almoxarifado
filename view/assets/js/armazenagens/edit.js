/**
 * Script para edição de locais de armazenagem
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar formulário
    iniciarFormulario();
});

/**
 * Inicializa o formulário de edição de armazenagem
 */
function iniciarFormulario() {
    document.getElementById('formArmazenagem').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obter dados do formulário
        const formData = new FormData(this);
        const DOMAIN = document.body.getAttribute('data-domain') || '';
        
        // Obter o ID da armazenagem da URL
        const urlParts = window.location.pathname.split('/');
        const armazenagemId = urlParts[urlParts.length - 1];
        
        // Enviar dados para o servidor
        fetch(`${DOMAIN}/armazenagens/${armazenagemId}/update`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Armazenagem atualizada!',
                    text: 'O local de armazenagem foi atualizado com sucesso.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = `${DOMAIN}/armazenagens`;
                });
            } else {
                // Erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.error || 'Ocorreu um erro ao atualizar a armazenagem.',
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