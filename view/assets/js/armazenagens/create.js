/**
 * Script para criação de locais de armazenagem
 * Agora usando o sistema global de configurações
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOMAIN agora está disponível globalmente
    console.log('🌐 DOMAIN global:', DOMAIN);
    
    // Inicializar formulário
    iniciarFormulario();
});

/**
 * Inicializa o formulário de criação de armazenagem
 */
function iniciarFormulario() {
    document.getElementById('formArmazenagem').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obter dados do formulário
        const formData = new FormData(this);
        
        // Usar a função global para construir a URL
        const url = buildUrl('/armazenagens/store');
        ajaxRequest(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Armazenagem cadastrada!',
                    text: 'O local de armazenagem foi adicionado com sucesso.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Usar buildUrl para redirecionamento
                    window.location.href = buildUrl('/armazenagens');
                });
            } else {
                // Erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Ocorreu um erro ao cadastrar a armazenagem.',
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