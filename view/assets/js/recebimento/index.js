/**
 * Script para gerenciamento de notas fiscais
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable para recebimentos
    // O sistema de datatable já está configurado no componente datatable.twig
    // Apenas precisamos garantir que a API retorne os dados no formato correto
    
    // Configurar função de marcar como recebida global
    window.marcarRecebida = function(id) {
        const DOMAIN = document.body.getAttribute('data-domain') || '';
        
        Swal.fire({
            title: 'Confirmação',
            text: 'Confirmar recebimento desta nota fiscal?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, confirmar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(buildUrl(`/recebimento/${id}/marcar-recebida`), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.success,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Recarregar DataTable
                            reloadDataTable('recebimentos');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.error || 'Ocorreu um erro ao processar a solicitação.',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao marcar nota fiscal como recebida. Tente novamente mais tarde.',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    };
});