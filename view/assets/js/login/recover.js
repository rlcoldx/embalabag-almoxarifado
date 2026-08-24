$(document).ready(function () {
    $('#recoverForm').submit(function (e) {
        const DOMAIN = $('body').data('domain');

        $('#recoverError').addClass('d-none').empty();
        $('#recoverSuccess').addClass('d-none').empty();
        e.preventDefault();

        $('#recoverBtn').prop('disabled', true);
        $('#recoverBtn .btn-text').addClass('d-none');
        $('#recoverBtn .btn-loading').removeClass('d-none');

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        $.ajax({
            url: DOMAIN + '/login/recover',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    let mensagem = resp.message || 'Se este e-mail estiver cadastrado, você receberá as instruções.';
                    if (resp.reset_url) {
                        mensagem += ' Ambiente local: <a href="' + resp.reset_url + '">abrir link de redefinição</a>.';
                    }
                    $('#recoverSuccess').removeClass('d-none').html(mensagem);
                } else {
                    $('#recoverError').removeClass('d-none').text(resp.error || 'Não foi possível enviar as instruções.');
                }
            },
            error: function () {
                $('#recoverError').removeClass('d-none').text('Erro ao enviar. Tente novamente.');
            },
            complete: function () {
                $('#recoverBtn').prop('disabled', false);
                $('#recoverBtn .btn-text').removeClass('d-none');
                $('#recoverBtn .btn-loading').addClass('d-none');
            }
        });
    });
});
