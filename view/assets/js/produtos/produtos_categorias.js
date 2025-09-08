$(document).ready(function () {


	$('#save_category').submit(function(e){

		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		// Obter dados do formulário
		const formData = new FormData(this);
		const data = Object.fromEntries(formData);
		
		let action = '';
        if($('#action').val() == 'cadastrar'){
            action = buildUrl('/produtos/categorias/save');
        }else{
            action = buildUrl('/produtos/categorias/save_edit');
        }
        
		$.ajax({
			url: action,
			data: JSON.stringify(data),
			type: 'POST',
			contentType: 'application/json',
			success: function(data){

				if (data == 'success') {

					Swal.fire({
						icon: 'success',
						title: 'SALVO COM SUCESSO!',
						text: 'A categoria foi salva com sucesso.',
						confirmButtonText: 'OK'
					}).then(() => {
						window.location.href = buildUrl('/produtos/categorias');
					});

				}else{

                    $('#salvar').html('SALVAR');
					$('#salvar').prop('type', 'submit');
					$('#salvar').removeClass('disabled');
					Swal.fire({ icon: 'error', title: 'ERRO AO SALVAR!', confirmButtonText: 'OK' }).then(() => {
						window.location.href = buildUrl('/produtos/categorias');
					});

				}
			}
		});
	});
  
});

function gerarSlug(str) {
    str = str.replace(/^\s+|\s+$/g, '');
    str = str.toLowerCase();
    var from = "ÁÄÂÀÃÅČÇĆĎÉĚËÈÊẼĔȆĞÍÌÎÏİŇÑÓÖÒÔÕØŘŔŠŞŤÚŮÜÙÛÝŸŽáäâàãåčçćďéěëèêẽĕȇğíìîïıňñóöòôõøðřŕšşťúůüùûýÿžþÞĐđßÆa·/_,:;";
    var to   = "AAAAAACCCDEEEEEEEEGIIIIINNOOOOOORRSSTUUUUUYYZaaaaaacccdeeeeeeeegiiiiinnooooooorrsstuuuuuyyzbBDdBAa------";
    for (var i=0, l=from.length ; i<l ; i++) {str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));}
    str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
    $('.cat_slug').val(str);
};