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
        console.warn('⚠️ Body não encontrado, tentando novamente...');
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

    /**
     * Faz uma requisição AJAX com configurações padrão
     * @param {string} url - URL da requisição
     * @param {object} options - Opções da requisição
     * @returns {Promise} Promise da requisição
     */
    window.ajaxRequest = function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                ...window.AppConfig.ajax.headers,
                ...(options.headers || {})
            },
            timeout: window.AppConfig.ajax.timeout
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        // Se a URL não for absoluta, usar o domínio base
        if (!url.startsWith('http')) {
            url = window.buildUrl(url);
        }
        
        return fetch(url, finalOptions);
    };

    /**
     * Faz uma requisição POST com JSON
     * @param {string} url - URL da requisição
     * @param {object} data - Dados para enviar
     * @param {object} options - Opções adicionais
     * @returns {Promise} Promise da requisição
     */
    window.postJson = function(url, data, options = {}) {
        return window.ajaxRequest(url, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            },
            ...options
        });
    };

    /**
     * Faz uma requisição GET com parâmetros
     * @param {string} url - URL base
     * @param {object} params - Parâmetros da query string
     * @param {object} options - Opções adicionais
     * @returns {Promise} Promise da requisição
     */
    window.getWithParams = function(url, params = {}, options = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        
        return window.ajaxRequest(fullUrl, {
            method: 'GET',
            ...options
        });
    };

    // ========================================
    // COMPATIBILIDADE COM JQUERY
    // ========================================

    // Se jQuery estiver disponível, adicionar métodos globais
    if (typeof $ !== 'undefined') {
        // Adicionar DOMAIN ao jQuery
        $.DOMAIN = DOMAIN;
        $.PATH = PATH;
        
        // Função para construir URLs
        $.buildUrl = window.buildUrl;
        $.buildAssetUrl = window.buildAssetUrl;
        
        // Configurar AJAX padrão do jQuery
        $.ajaxSetup({
            timeout: window.AppConfig.ajax.timeout,
            headers: window.AppConfig.ajax.headers
        });
        
        // Função para requisições POST com JSON
        $.postJson = function(url, data, success, dataType) {
            return $.ajax({
                url: window.buildUrl(url),
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: success,
                dataType: dataType || 'json'
            });
        };
    }

    // ========================================
    // EXPORT PARA MÓDULOS (se usando ES6 modules)
    // ========================================

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            DOMAIN,
            PATH,
            VERSION,
            LANGUAGE,
            AppConfig: window.AppConfig,
            buildUrl: window.buildUrl,
            buildAssetUrl: window.buildAssetUrl,
            ajaxRequest: window.ajaxRequest,
            postJson: window.postJson,
            getWithParams: window.getWithParams
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
