document.addEventListener('DOMContentLoaded', function () {
});

function deleteConferencia(id) {
    if (!confirm('Tem certeza que deseja excluir esta conferência?')) {
        return;
    }

    fetch(buildUrl('/conferencia/destroy/' + id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                if (typeof loadData === 'function') {
                    loadData('conferencias');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Sucesso', text: data.message });
                }
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Erro', text: data.message || 'Erro ao excluir conferência' });
            }
        })
        .catch(function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao excluir conferência' });
            }
        });
}
