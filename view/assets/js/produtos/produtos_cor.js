$(document).ready(function () {

	$('.hex_cor').on('input', function() {
		var hex = $(this).val().replace(/[^A-Fa-f0-9]/g, '');
		if (hex.length > 6) {
			hex = hex.substring(0, 6);
		}
		$(this).val('#' + hex);
	});

    $('#save_cor').submit(function(e){

		let DOMAIN = $('body').data('domain');
		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		let formData = new FormData(this);
		let action = '';
        

        if($('#action').val() == 'cadastrar'){
			action = DOMAIN + '/produtos/cores/save';
        }else{
            action = DOMAIN + '/produtos/cores/save_edit';
        }
        
		$.ajax({
			url: action,
			data: formData,
			type: 'POST',
			success: function(data){

				if (data == ' success') {

                    Swal.fire({icon: 'success', title: 'SALVO COM SUCESSO!', showConfirmButton: false, timer: 1500});
                    setTimeout(function(){
                        location.reload();
                    }, 1500);

				}else{

                    $('#salvar').html('SALVAR');
					$('#salvar').prop('type', 'submit');
					$('#salvar').removeClass('disabled');
                    Swal.fire({icon: 'error', title: 'ERRO AO SALVAR!', showConfirmButton: false, timer: 1500});

				}
			},
			processData: false,
			cache: false,
			contentType: false
		});
	});
  
});


function deletar_cor(id, cor_nome) {
	Swal.fire({
		title: "Deletar essa Cor?",
		text: "Excluindo uma Cor exclui as variaveis dos produtos com aquela Cor. Mas não exclui as variaveis já escolhida nos pedidos.",
		showCancelButton: true,
		cancelButtonText: 'Não',
		confirmButtonText: 'Sim',
		dangerMode: true,
	}).then((result) => {
		  if (result.value === true) {
			let DOMAIN = $('body').data('domain');
            $.ajax({
                url: DOMAIN + '/produtos/cores/excluir',
                data: {'id': id, 'cor_nome': cor_nome},
                type: 'post',
                success: function(data){
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                    Swal.fire('', 'Cor excluida com sucesso!', 'success');
                }
            });
		  }
	});
}