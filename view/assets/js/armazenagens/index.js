/**
 * Script para gerenciamento de armazenagens
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable para armazenagens
    // O sistema de datatable já está configurado no componente datatable.twig
    // Apenas precisamos garantir que a API retorne os dados no formato correto
    
    // Configurar função de exclusão global
    window.excluirArmazenagem = function(id) {
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Deseja realmente excluir esta armazenagem?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(buildUrl('/armazenagens/delete/' + id), {
                    method: 'POST',
                    processData: false,
                    contentType: false,
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
                            reloadDataTable('armazenagens');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.error || 'Ocorreu um erro ao excluir a armazenagem',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao excluir armazenagem. Tente novamente mais tarde.',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    };
});