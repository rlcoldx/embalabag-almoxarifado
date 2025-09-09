/**
 * Configurações Globais do Sistema
 * Este arquivo define variáveis e constantes globais que podem ser usadas em qualquer arquivo JS
 */

// ========================================
// FUNÇÃO PARA INICIALIZAR CONFIGURAÇÕES
// ========================================

function initializeGlobalConfig() {
    // Verificar se o body existe
    if (!document.body) {
        setTimeout(initializeGlobalConfig, 10);
        return;
    }

    // ========================================
    // CONSTANTES GLOBAIS
    // ========================================

    // DOMAIN - Domínio base do sistema
    window.DOMAIN = document.body.getAttribute('data-domain') || '';

    // PATH - Caminho base dos assets
    window.PATH = document.body.getAttribute('data-path') || '';

    // VERSION - Versão do sistema
    window.VERSION = document.body.getAttribute('data-version') || '1.0.0';

    // LANGUAGE - Idioma do sistema
    window.LANGUAGE = document.body.getAttribute('data-lang') || 'pt-BR';

    // Tornar as constantes disponíveis globalmente
    const DOMAIN = window.DOMAIN;
    const PATH = window.PATH;
    const VERSION = window.VERSION;
    const LANGUAGE = window.LANGUAGE;

    // ========================================
    // VARIÁVEIS GLOBAIS
    // ========================================

    // Objeto global com todas as configurações
    window.AppConfig = {
        domain: DOMAIN,
        path: PATH,
        version: VERSION,
        language: LANGUAGE,
        
        // URLs comuns
        urls: {
            api: DOMAIN + '/api',
            upload: DOMAIN + '/upload',
            assets: PATH + '/view/assets',
            images: PATH + '/view/assets/images',
            css: PATH + '/view/assets/css',
            js: PATH + '/view/assets/js'
        },
        
        // Configurações de AJAX padrão
        ajax: {
            timeout: 30000,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        }
    };

    // ========================================
    // FUNÇÕES UTILITÁRIAS GLOBAIS
    // ========================================

    /**
     * Constrói uma URL completa usando o domínio base
     * @param {string} path - Caminho relativo
     * @returns {string} URL completa
     */
    window.buildUrl = function(path) {
        if (path.startsWith('http')) {
            return path;
        }
        return DOMAIN + (path.startsWith('/') ? path : '/' + path);
    };

    /**
     * Constrói uma URL para assets
     * @param {string} assetPath - Caminho do asset
     * @returns {string} URL completa do asset
     */
    window.buildAssetUrl = function(assetPath) {
        return PATH + (assetPath.startsWith('/') ? assetPath : '/' + assetPath);
    };

    // ========================================
    // EXPORT PARA MÓDULOS (se usando ES6 modules)
    // ========================================

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            DOMAIN,
            PATH,
            VERSION,
            LANGUAGE
        };
    }
}

// ========================================
// INICIALIZAÇÃO
// ========================================

// Tentar inicializar imediatamente se o DOM já estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeGlobalConfig);
} else {
    // DOM já está pronto
    initializeGlobalConfig();
}
