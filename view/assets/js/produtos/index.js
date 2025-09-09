document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable para produtos
    // O sistema de datatable já está configurado no componente datatable.twig
    // Apenas precisamos garantir que a API retorne os dados no formato correto
    
    // Configurar função de exclusão global
    // window.excluirProduto = function(id) {
    //     Swal.fire({
    //         title: 'Confirma a exclusão?',
    //         text: "Esta ação não pode ser revertida!",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#d9534f',
    //         cancelButtonColor: '#6c757d',
    //         confirmButtonText: 'Sim, excluir!',
    //         cancelButtonText: 'Cancelar'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             fetch(buildUrl(`/produtos/delete/${id}`), {
    //                 method: 'POST',
    //                 headers: {
    //                     'Content-Type': 'application/json',
    //                 }
    //             })
    //             .then(response => response.json())
    //             .then(data => {
    //                 if (data.success) {
    //                     Swal.fire({
    //                         icon: 'success',
    //                         title: 'Sucesso!',
    //                         text: data.message || 'Produto excluído com sucesso',
    //                         confirmButtonText: 'OK'
    //                     }).then(() => {
    //                         // Recarregar DataTable
    //                         reloadDataTable('produtos');
    //                     });
    //                 } else {
    //                     Swal.fire({
    //                         icon: 'error',
    //                         title: 'Erro!',
    //                         text: data.error || 'Erro ao excluir produto',
    //                         confirmButtonText: 'OK'
    //                     });
    //                 }
    //             })
    //             .catch(error => {
    //                 console.error('Erro:', error);
    //                 Swal.fire({
    //                     icon: 'error',
    //                     title: 'Erro!',
    //                     text: 'Erro ao excluir produto',
    //                     confirmButtonText: 'OK'
    //                 });
    //             });
    //         }
    //     });
    // };
});