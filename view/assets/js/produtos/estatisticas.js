/**
 * Script para carregar estatísticas de produtos e gerenciar filtros rápidos
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Carregar estatísticas dos produtos
    carregarEstatisticas();
    
    // Configurar botões de filtro rápido
    configurarFiltrosRapidos();

    // Botão de exportar
    document.getElementById('btn-export').addEventListener('click', function() {
        exportToCSV('produtos-datatable', 'produtos');
    });
});

/**
 * Carrega as estatísticas dos produtos do servidor
 */
function carregarEstatisticas() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    fetch(`${DOMAIN}/produtos/estatisticas`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-produtos').textContent = data.total_produtos || 0;
                document.getElementById('estoque-baixo').textContent = data.estoque_baixo || 0;
                document.getElementById('total-categorias').textContent = data.total_categorias || 0;
                document.getElementById('valor-estoque').textContent = 'R$ ' + (data.valor_estoque || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        })
        .catch(error => {
            console.error('Erro ao carregar estatísticas:', error);
            document.getElementById('total-produtos').textContent = '-';
            document.getElementById('estoque-baixo').textContent = '-';
            document.getElementById('total-categorias').textContent = '-';
            document.getElementById('valor-estoque').textContent = '-';
        });
}

/**
 * Configura os botões de filtro rápido para a tabela de produtos
 */
function configurarFiltrosRapidos() {
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            // Remover classe active de todos os botões
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Adicionar classe active ao botão clicado
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            let filterValue = '';
            
            // Definir valor do filtro com base no botão clicado
            if (filter === 'estoque-baixo') {
                filterValue = 'baixo';
            } else if (filter === 'estoque-normal') {
                filterValue = 'normal';
            } else if (filter === 'inativos') {
                // Aplicar filtro para produtos inativos
                reloadDataTable({
                    filters: {
                        status: 'inativo'
                    }
                });
                return;
            }
            
            // Recarregar DataTable com o filtro selecionado
            if (filter === 'all') {
                reloadDataTable();
            } else {
                reloadDataTable({
                    filters: {
                        estoque: filterValue
                    }
                });
            }
        });
    });
}