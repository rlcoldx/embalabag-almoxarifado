/**
 * Script para criação de produtos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar formulário de criação de produto
    iniciarFormulario();
});

/**
 * Inicializa o formulário de criação de produto
 */
function iniciarFormulario() {
    document.getElementById('formProduto').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obter dados do formulário
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Enviar dados para o servidor
        fetch(buildUrl('/produtos/store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Feedback de sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.success,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Redirecionar para lista de produtos
                    window.location.href = buildUrl('/produtos');
                });
            } else {
                // Feedback de erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.error || 'Ocorreu um erro ao salvar o produto',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            // Feedback de erro inesperado
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao salvar produto. Verifique os dados e tente novamente.',
                confirmButtonText: 'OK'
            });
        });
    });
}