$(document).ready(function () {


    $('#save_category').submit(function(e){

		let DOMAIN = $('body').data('domain');
		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		let formData = new FormData(this);
		let action = '';
        

        if($('#action').val() == 'cadastrar'){
            action = DOMAIN + '/produtos/categorias/save';
        }else{
            action = DOMAIN + '/produtos/categorias/save_edit';
        }
        
		$.ajax({
			url: action,
			data: formData,
			type: 'POST',
			success: function(data){

				if (data == ' success') {

					Swal.fire({
						icon: 'success',
						title: 'SALVO COM SUCESSO!',
						text: 'A categoria foi salva com sucesso.',
						confirmButtonText: 'OK'
					}).then(() => {
						window.location.href = `${DOMAIN}/produtos/categorias`;
					});

				}else{

                    $('#salvar').html('SALVAR');
					$('#salvar').prop('type', 'submit');
					$('#salvar').removeClass('disabled');
					Swal.fire({ icon: 'error', title: 'ERRO AO SALVAR!', confirmButtonText: 'OK' }).then(() => {
						window.location.href = `${DOMAIN}/produtos/categorias`;
					});

				}
			},
			processData: false,
			cache: false,
			contentType: false
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