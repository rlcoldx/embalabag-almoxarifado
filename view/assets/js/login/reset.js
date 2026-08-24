$(document).ready(function () {
    $('#resetForm').submit(function (e) {
        const DOMAIN = $('body').data('domain');

        $('#resetError').addClass('d-none');
        $('#resetSuccess').addClass('d-none');
        e.preventDefault();

        $('#resetBtn').prop('disabled', true);
        $('#resetBtn .btn-text').addClass('d-none');
        $('#resetBtn .btn-loading').removeClass('d-none');

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        $.ajax({
            url: DOMAIN + '/login/reset',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    $('#resetSuccess').removeClass('d-none').text(resp.message || 'Senha redefinida com sucesso.');
                    setTimeout(function () {
                        window.location.href = resp.redirect || DOMAIN + '/login';
                    }, 800);
                } else {
                    $('#resetError').removeClass('d-none').text(resp.error || 'Não foi possível redefinir a senha.');
                }
            },
            error: function () {
                $('#resetError').removeClass('d-none').text('Erro ao salvar. Tente novamente.');
            },
            complete: function () {
                $('#resetBtn').prop('disabled', false);
                $('#resetBtn .btn-text').removeClass('d-none');
                $('#resetBtn .btn-loading').addClass('d-none');
            }
        });
    });
});
