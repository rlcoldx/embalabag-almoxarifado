$(document).ready(function () {

    $('select[name="promocao_tipo"]').change(function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'porcentagem') {
            $('.porcentagem').show();
        }else{
            $('.porcentagem').hide();
        }
    });

    $('input[name="porcentagem"]').on('change keyup', function() {
        var valor = parseFloat($('input[name="valor"]').val());
        var porcentagem = parseFloat($('input[name="porcentagem"]').val());

        if (!isNaN(valor) && !isNaN(porcentagem)) {
            var desconto = (valor * porcentagem) / 100;
            var valorFinal = valor - desconto;
            // Formata o valor para o formato de moeda brasileira
            var valorFormatado = valorFinal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
            $('input[name="promocao"]').val(valorFormatado);
        } else {
            $('input[name="promocao"]').val('');
        }
    });

    // Função para verificar os pais
    function verificarPais(checkbox) {
        const paiId = checkbox.data('parent');

        // Se o checkbox filho estiver marcado
        if (checkbox.is(':checked')) {
            // Se existir um pai
            if (paiId) {
                const paiCheckbox = $('.form-check-input[data-id="' + paiId + '"]');

                // Se o pai ainda não estiver marcado
                if (!paiCheckbox.is(':checked')) {
                // Marcar o pai
                paiCheckbox.prop('checked', true);

                // Verificar os pais do pai
                verificarPais(paiCheckbox);
                }
            }
        }
    }

    // Evento para verificar os pais ao clicar em um filho
    $('.categories_check').change(function () {
        verificarPais($(this));
    });
   
    if ($(".tags").length > 0) {
        $('.tags').tagsinput();
    }

    if ($("#texto").length > 0) {
        new FroalaEditor('#texto', {
            key: "1C%kZV[IX)_SL}UJHAEFZMUJOYGYQE[\\ZJ]RAe(+%$==",
            enter: FroalaEditor.ENTER_BR,
            placeholderText: 'Descrição do produto...',
            heightMin: 100,
            language: 'pt_br',
            pastePlain: true, // Habilita a limpeza HTML
            pasteDeniedTags: ['script', 'style'], // Remove tags específicas
            pasteDeniedAttrs: ['id', 'class'], // Remove atributos específicos
            pasteAllowedStyleProps: [], // Remove estilos inline
            attribution: false,
            toolbarButtons: {
                'moreText': {
                'buttons': ['bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'clearFormatting', 'formatOL', 'formatUL', 'outdent', 'indent'],
                'buttonsVisible': 6
                },
                'moreParagraph': {
                'buttons': ['alignLeft', 'alignCenter',  'alignRight']
                },
                'moreRich': {
                'buttons': ['emoticons', 'fontAwesome']
                },
                'moreMisc': {
                'buttons': ['undo', 'redo'],
                'align': 'right'
                }
            }
        });
    }
	
	//SALVA produto
	$('.novo_produto').focusout(function(e){

        e.preventDefault();
        var nome = $(this).val();
        var slug = format_slug(nome, '');

        if(nome != ''){
            var DOMAIN = $('body').data('domain');
            $.ajax({
                url: DOMAIN + '/produtos/save_draft',
                data: {'nome': nome, 'slug': slug},
                type: 'POST',
                success: function(data){
                    $('#id_produto').val(data.id);
                    $('#produto_nome').removeClass('novo_produto');
                    $('#produto_nome').addClass('editar_produto');

                    var new_url = DOMAIN+'/produtos/edit/'+data.id;
                    window.history.pushState('data','Title', new_url);
                    document.nome = 'Editar: '+data.nome;

                    $('.edit-slug-box').show();
                    start_product_gallery();
                }
            });
        }
    });

	//EDITAR produto
    $('#cadastrar_produto').submit(function(e){

		$(this).children(':input[value=""]').attr("disabled", "disabled");
		var DOMAIN = $('body').data('domain');
		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		var formData = new FormData(this);
        
		$.ajax({
			url: DOMAIN + '/produtos/editar/save',
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

    if ($(".repeater").length > 0) {
        $('.repeater').each(function(index) {
            if (!$(this).hasClass('repeater-initialized')) {
                $(this).addClass('repeater-initialized');
                $(this).repeater({
                    show: function () {
                        $(this).slideDown();
                        reloadScript();
                    },
                    hide: function (deleteElement) {
                        if(confirm('Tem certeza de que deseja excluir esse item?')) {
                            $(this).slideUp(deleteElement);
                        }
                        reloadScript();
                    },
                    ready: function(setIndexes) {
                        
                    },
                    isFirstItemUndeletable: false
                });
            }
        });
    }

    $('.money').mask("#.##0,00", {reverse: true});

    if ($('.sumoselect').length) {
    	$('.sumoselect').SumoSelect({
			search : true,
			placeholder: 'Selecione uma Cor',
    		searchText : 'Pesquisar',
            triggerChangeCombined: true
		});
    }

});

function reloadScript() {
    $('.money').mask("#.##0,00", {reverse: true});
    setTimeout(function() {
        $(".repeater input").each(function(){
            if($(this).val() === ""){
                $(this).val($(this).attr("min"));
            }
        });
        $(".repeater select").each(function(){
            if($(this).val() == null){
                $(this).val($(this).find("option:first").val());
            }
        });
        
        // Reinicializar sumoselect nos novos campos
        $('.repeater .sumoselect').each(function(){
            if(!$(this).hasClass('SumoSelect')){
                $(this).SumoSelect({
                    search : true,
                    placeholder: 'Selecione uma Empresa',
                    searchText : 'Pesquisar',
                    triggerChangeCombined: true
                });
            }
        });
    }, 500);
}

// Função para alternar a classe "active" no label
function toggleCheckbox(checkbox) {
    var label = checkbox.parentElement;
    if (checkbox.checked) {
        label.classList.add("active");
    } else {
        label.classList.remove("active");
    }
}

function format_slug(nome, slug) {
    nome = nome.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
    nome = nome.replace(/[^a-z0-9 ]/gi, '');
    nome = nome.replace(/ +(?= )/g,'');
    nome = nome.replace(/^-+/, '');
    nome = nome.replace(/ /g, '-');
    if(slug != ''){
        $('#slug').val(nome.toLowerCase());
    }else{
        return nome.toLowerCase();
    }
}

function deleteProduto(id_produto) {
	Swal.fire({
		title: "Deletar esse Produto?",
		text: "Tem certeza que deseja excluir esse Produto. Essa ação não poderá ser desfeita.",
		showCancelButton: true,
		cancelButtonText: 'Não',
		confirmButtonText: 'Sim',
		dangerMode: true,
	}).then((result) => {
		  if (result.value === true) {
			let DOMAIN = $('body').data('domain');
            $.ajax({
                url: DOMAIN + '/produtos/excluir',
                data: {'id_produto': id_produto},
                type: 'post',
                success: function(data){
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                    Swal.fire('', 'Produto excluido com sucesso!', 'success');
                }
            });
		  }
	});
}

$('.select2-blacklist').select2({
    placeholder: 'Selecione as empresas...',
    allowClear: true,
    width: '100%'
});