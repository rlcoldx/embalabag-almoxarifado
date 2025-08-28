/**
 * Script para o mapa de armazenagens
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar filtros por tipo
    initFiltrosTipo();
    
    // Inicializar filtros por capacidade
    initFiltrosCapacidade();
});

/**
 * Inicializa os filtros por tipo de armazenagem
 */
function initFiltrosTipo() {
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            // Remover classe active de todos os botões
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('active');
            });
            // Adicionar classe active ao botão clicado
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            // Filtrar os cards
            document.querySelectorAll('[data-filter]').forEach(card => {
                if (card.tagName !== 'BUTTON') { // Ignorar os botões de filtro
                    if (filterValue === 'all' || card.getAttribute('data-filter') === filterValue) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });
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
                
                const capacityValue = this.getAttribute('data-capacity');
                
                // Filtrar os cards
                document.querySelectorAll('[data-capacity]').forEach(card => {
                    if (card.tagName !== 'BUTTON') { // Ignorar os botões de filtro
                        if (capacityValue === 'all' || card.getAttribute('data-capacity') === capacityValue) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        }
    });
}