$(document).ready(function () {


	$('#loginForm').submit(function(e){

		let DOMAIN = $('body').data('domain');

		// reset alerts
		$('#loginError').addClass('d-none');
		$('#loginSuccess').addClass('d-none');
		e.preventDefault();

		// loading state
		$('#loginBtn').prop('disabled', true);
		$('#loginBtn .btn-text').addClass('d-none');
		$('#loginBtn .btn-loading').removeClass('d-none');

		// dados do formulário em JSON
		const formData = new FormData(this);
		const data = Object.fromEntries(formData);

		$.ajax({
			url: DOMAIN + '/login',
			type: 'POST',
			data: JSON.stringify(data),
			contentType: 'application/json',
			dataType: 'json',
			success: function (resp) {
				if (resp.success) {
					$('#loginSuccess').removeClass('d-none').text(resp.message || 'Login realizado com sucesso!');
					setTimeout(function () {
						window.location.href = resp.redirect || DOMAIN + '/';
					}, 700);
				} else {
					$('#loginError').removeClass('d-none').text(resp.error || 'Login inválido');
				}
			},
			error: function (xhr) {
				let errorMsg = 'Erro ao tentar logar. Tente novamente.';
				if (xhr.responseJSON && xhr.responseJSON.error) {
					errorMsg = xhr.responseJSON.error;
				}
				$('#loginError').removeClass('d-none').text(errorMsg);
			},
			complete: function () {
				$('#loginBtn').prop('disabled', false);
				$('#loginBtn .btn-text').removeClass('d-none');
				$('#loginBtn .btn-loading').addClass('d-none');
			}
		});
	});

});