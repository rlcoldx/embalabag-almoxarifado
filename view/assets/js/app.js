// Sistema de Submenus com jQuery
function initializeApp() {    
    // Definir variáveis globais apenas quando jQuery estiver disponível
    if (typeof $ !== 'undefined') {
        window.pach = $('body').data('pach');
        window.DOMAIN = $('body').data('dominio');
        window.version = $('body').data('version');
        window.lang = $('body').data('lang');
    }
    
    // Sistema de submenus
    $('.slide.has-sub > a').on('click', function (e) {
        e.preventDefault();

        var $parent = $(this).parent();
        var $submenu = $parent.find('.slide-menu');
        var $icon = $(this).find('.side-menu__angle');

        // Remover classe 'open' de TODOS os menus
        $('.slide.has-sub').removeClass('open');
        $('.slide-menu').slideUp(300);
        $('.side-menu__angle').removeClass('rotate');

        // Adicionar classe 'open' apenas no menu clicado
        if (!$parent.hasClass('open')) {
            $parent.addClass('open');
            $submenu.slideDown(300);
            $icon.addClass('rotate');
        }
    });

    // Fechar submenus ao clicar fora
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.slide.has-sub').length) {
            $('.slide.has-sub').removeClass('open');
            $('.slide-menu').slideUp(300);
            $('.side-menu__angle').removeClass('rotate');
        }
    });

    // Sistema de DataTable simplificado
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').each(function () {
            if (!$(this).hasClass('dataTable')) {
                $(this).DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                    }
                });
            }
        });
    }

    // Sistema de Choices simplificado
    if (typeof Choices !== 'undefined') {
        $('.choices-images').each(function () {
            if (!$(this).hasClass('choices__input')) {
                new Choices(this, {
                    choices: [
                        { value: "1", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/us_flag.jpg" alt=""> <span class="mx-1">United States</span>', id: 1 },
                        { value: "2", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/spain_flag.jpg" alt=""> <span class="ms-1">Spain</span>', id: 2 },
                        { value: "3", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/french_flag.jpg" alt=""> <span class="ms-1">France</span>', id: 3 },
                        { value: "4", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/germany_flag.jpg" alt=""> <span class="ms-1">Germany</span>', id: 4 },
                        { value: "5", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/italy_flag.jpg" alt=""> <span class="ms-1">Italy</span>', id: 5 },
                        { value: "6", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/russia_flag.jpg" alt=""> <span class="ms-1">Netherlands</span>', id: 6 },
                        { value: "7", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/argentina_flag.jpg" alt=""> <span class="ms-1">Argentina</span>', id: 7 },
                        { value: "8", label: '<img class="avatar avatar-xs avatar-rounded" src="../assets/images/flags/argentina_flag.jpg" alt=""> <span class="ms-1">Argentina</span>', id: 8 }
                    ]
                });
            }
        });
    }
}

// Aguardar jQuery estar disponível
if (typeof $ !== 'undefined') {
    // jQuery já está disponível
    $(document).ready(initializeApp);
} else {
    // Aguardar jQuery carregar
    document.addEventListener('DOMContentLoaded', function() {
        // Tentar várias vezes até o jQuery estar disponível
        let attempts = 0;
        const maxAttempts = 50;
        
        function checkJQuery() {
            if (typeof $ !== 'undefined') {
                $(document).ready(initializeApp);
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(checkJQuery, 100);
            } else {}
        }
        
        checkJQuery();
    });
}