// Script para página de lista de cargos
document.addEventListener('DOMContentLoaded', function() {
    // Função para excluir cargo
    window.deleteCargo = function(cargoId) {
        if (confirm('Tem certeza que deseja excluir este cargo?')) {
            fetch(DOMAIN + '/cargos/delete/' + cargoId, {
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
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.error
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao excluir cargo'
                });
            });
        }
    };
}); 