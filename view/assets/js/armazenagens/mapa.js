/**
 * Script para o mapa de armazenagens
 */

// Variáveis globais para armazenar os filtros ativos
let filtroAtivo = {
    setor: 'all',
    codigo: 'all',
    capacidade: 'all'
};

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar filtros por corredor
    initFiltroSetor();
    
    // Inicializar filtros por andar
    initFiltroCodigo();
    
    // Inicializar filtros por capacidade
    initFiltrosCapacidade();
});

/**
 * Inicializa os filtros por corredor (setor)
 */
function initFiltroSetor() {
    const selectSetor = document.querySelector('[data-filter-setor]');
    if (selectSetor) {
        selectSetor.addEventListener('change', function() {
            filtroAtivo.setor = this.value;
            aplicarFiltros();
        });
    }
}

/**
 * Inicializa os filtros por andar (codigo)
 */
function initFiltroCodigo() {
    const selectCodigo = document.querySelector('[data-filter-codigo]');
    if (selectCodigo) {
        selectCodigo.addEventListener('change', function() {
            filtroAtivo.codigo = this.value;
            aplicarFiltros();
        });
    }
}

/**
 * Inicializa os filtros por capacidade de armazenagem
 */
function initFiltrosCapacidade() {
    document.querySelectorAll('[data-capacity]').forEach(button => {
        if (button.tagName === 'BUTTON') { // Considerar apenas os botões de filtro
            button.addEventListener('click', function() {
                // Remover classe active de todos os botões
                document.querySelectorAll('[data-capacity]').forEach(btn => {
                    if (btn.tagName === 'BUTTON') {
                        btn.classList.remove('active');
                    }
                });
                // Adicionar classe active ao botão clicado
                this.classList.add('active');
                
                filtroAtivo.capacidade = this.getAttribute('data-capacity');
                aplicarFiltros();
            });
        }
    });
}

/**
 * Aplica todos os filtros ativos aos cards
 */
function aplicarFiltros() {
    document.querySelectorAll('[data-setor][data-codigo][data-capacity]').forEach(card => {
        const setorCard = card.getAttribute('data-setor');
        const codigoCard = card.getAttribute('data-codigo');
        const capacidadeCard = card.getAttribute('data-capacity');
        
        const mostrarSetor = filtroAtivo.setor === 'all' || setorCard === filtroAtivo.setor;
        const mostrarCodigo = filtroAtivo.codigo === 'all' || codigoCard === filtroAtivo.codigo;
        const mostrarCapacidade = filtroAtivo.capacidade === 'all' || capacidadeCard === filtroAtivo.capacidade;
        
        if (mostrarSetor && mostrarCodigo && mostrarCapacidade) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}