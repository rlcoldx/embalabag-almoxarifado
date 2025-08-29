/**
 * Script para a página de conferência
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de conferência carregada');
    
    // O sistema de DataTable é inicializado automaticamente pelo componente datatable.twig
    // Não precisamos fazer nada aqui, apenas aguardar o carregamento
});

// Função para excluir conferência (chamada pelo botão de ações)
function deleteConferencia(id) {
    if (confirm('Tem certeza que deseja excluir esta conferência?')) {
        fetch(`/conferencia/destroy/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recarregar a tabela
                if (typeof loadData === 'function') {
                    loadData('conferencias');
                }
                // Mostrar mensagem de sucesso
                if (typeof showAlert === 'function') {
                    showAlert('Conferência excluída com sucesso!', 'success');
                }
            } else {
                console.error('Erro ao excluir conferência:', data.message);
                if (typeof showAlert === 'function') {
                    showAlert('Erro ao excluir conferência: ' + data.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            if (typeof showAlert === 'function') {
                showAlert('Erro ao excluir conferência', 'error');
            }
        });
    }
}
