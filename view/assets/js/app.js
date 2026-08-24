(function () {
    if (window.__embalabagThemeInit) {
        return;
    }
    window.__embalabagThemeInit = true;

    function applyTheme(mode) {
        var html = document.documentElement;
        if (mode === 'dark') {
            html.setAttribute('data-nav-layout', html.getAttribute('data-nav-layout') || 'vertical');
            html.setAttribute('data-theme-mode', 'dark');
            html.setAttribute('data-header-styles', 'dark');
            html.setAttribute('data-menu-styles', 'dark');
            localStorage.setItem('ynexdarktheme', 'true');
            localStorage.setItem('ynexMenu', 'dark');
            localStorage.setItem('ynexHeader', 'dark');
            return;
        }

        html.setAttribute('data-nav-layout', html.getAttribute('data-nav-layout') || 'vertical');
        html.setAttribute('data-theme-mode', 'light');
        html.setAttribute('data-header-styles', 'light');
        html.setAttribute('data-menu-styles', 'dark');
        localStorage.removeItem('ynexdarktheme');
        localStorage.removeItem('ynexHeader');
    }

    applyTheme(localStorage.getItem('ynexdarktheme') ? 'dark' : (document.documentElement.getAttribute('data-theme-mode') || 'light'));

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.layout-setting');
        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        var isDark = document.documentElement.getAttribute('data-theme-mode') === 'dark';
        applyTheme(isDark ? 'light' : 'dark');
    }, true);
})();

(function () {
    function isMobile() {
        return window.innerWidth < 992;
    }

    function overlay() {
        return document.getElementById('responsive-overlay');
    }

    function closeMobileSidebar() {
        document.documentElement.setAttribute('data-toggled', 'close');
        var layer = overlay();
        if (layer) {
            layer.classList.remove('active');
        }
    }

    function openMobileSidebar() {
        document.documentElement.setAttribute('data-toggled', 'open');
        var layer = overlay();
        if (layer) {
            layer.classList.add('active');
        }
    }

    function syncMobileSidebar() {
        var html = document.documentElement;
        var state = html.getAttribute('data-toggled');

        if (isMobile()) {
            if (state !== 'open') {
                closeMobileSidebar();
            }
            return;
        }

        if (state === 'close' || state === 'open') {
            html.removeAttribute('data-toggled');
        }
        var layer = overlay();
        if (layer) {
            layer.classList.remove('active');
        }
    }

    syncMobileSidebar();

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('.sidemenu-toggle');
        if (toggle && isMobile()) {
            event.preventDefault();
            if (document.documentElement.getAttribute('data-toggled') === 'open') {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
            return;
        }

        if (event.target.closest('#responsive-overlay')) {
            closeMobileSidebar();
            return;
        }

        var link = event.target.closest('.app-sidebar a.side-menu__item[href]');
        if (link && isMobile() && link.getAttribute('href') && link.getAttribute('href').indexOf('javascript') !== 0) {
            closeMobileSidebar();
        }
    }, true);

    window.addEventListener('resize', syncMobileSidebar);
})();

// Sistema de Submenus com jQuery

/**
 * Mantém o menu principal aberto quando um sub-item está ativo
 */
function openActiveMenuParents() {
    if (typeof $ === 'undefined') {
        return;
    }

    $('.slide-menu .slide.active').each(function () {
        var $parent = $(this).closest('.slide.has-sub');
        if ($parent.length) {
            $parent.addClass('open active');
            $parent.children('.slide-menu').show();
            $parent.children('a').find('.side-menu__angle').addClass('rotate');
        }
    });

    $('.slide.has-sub.open').each(function () {
        $(this).children('.slide-menu').show();
        $(this).children('a').find('.side-menu__angle').addClass('rotate');
    });
}

function initializeApp() {
    if (window.__embalabagAppInit) {
        return;
    }
    window.__embalabagAppInit = true;

    // Definir variáveis globais apenas quando jQuery estiver disponível
    if (typeof $ !== 'undefined') {
        window.pach = $('body').data('pach');
        window.DOMAIN = $('body').data('dominio');
        window.version = $('body').data('version');
        window.lang = $('body').data('lang');
    }
    
    // Sistema de submenus
    openActiveMenuParents();

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

    // Fechar submenus ao clicar fora (sem interferir no toggle do sidebar)
    $(document).on('click', function (e) {
        if ($(e.target).closest('.slide.has-sub, .sidemenu-toggle, .app-header, #responsive-overlay').length) {
            return;
        }
        $('.slide.has-sub').removeClass('open');
        $('.slide-menu').slideUp(300);
        $('.side-menu__angle').removeClass('rotate');
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