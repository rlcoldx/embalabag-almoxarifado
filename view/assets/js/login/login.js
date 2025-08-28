$(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        let DOMAIN = $('body').data('domain');

        // Reset alerts
        $('#loginError').addClass('d-none');
        $('#loginSuccess').addClass('d-none');

        // Show loading state
        $('#loginBtn').prop('disabled', true);
        $('#loginBtn .btn-text').addClass('d-none');
        $('#loginBtn .btn-loading').removeClass('d-none');

        $.ajax({
            url: DOMAIN + '/login',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    $('#loginSuccess').removeClass('d-none').text(resp.message || 'Login realizado com sucesso!');

                    // Redirect after a short delay
                    setTimeout(function () {
                        window.location.href = resp.redirect || DOMAIN + '/';
                    }, 1000);
                } else {
                    $('#loginError').removeClass('d-none').text(resp.error || 'Login inválido');
                }
            },
            error: function (xhr, status, error) {
                let errorMsg = 'Erro ao tentar logar. Tente novamente.';

                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }

                $('#loginError').removeClass('d-none').text(errorMsg);
            },
            complete: function () {
                // Reset button state
                $('#loginBtn').prop('disabled', false);
                $('#loginBtn .btn-text').removeClass('d-none');
                $('#loginBtn .btn-loading').addClass('d-none');
            }
        });
    });
});