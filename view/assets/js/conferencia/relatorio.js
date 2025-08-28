/**
 * Script para a página de relatório de conferência
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de relatório de conferência carregada');
    
    // Carregar dados iniciais
    carregarEstatisticas();
    carregarTabela();
    
    // Configurar filtros
    configurarFiltros();
});

function carregarEstatisticas() {
    // Simular carregamento de estatísticas
    // Em produção, isso viria de uma API
    document.getElementById('total-conferencias').textContent = '0';
    document.getElementById('conferencias-pendentes').textContent = '0';
    document.getElementById('conferencias-concluidas').textContent = '0';
    document.getElementById('conferencias-andamento').textContent = '0';
}

function carregarTabela() {
    const tbody = document.querySelector('#tabela-relatorio tbody');
    if (!tbody) return;
    
    // Simular dados de exemplo
    const dados = [
        {
            id: 1,
            nfe: 'NFE001',
            fornecedor: 'Fornecedor A',
            produto: 'Produto 1',
            status: 'Concluída',
            qualidade: 'Aprovado',
            data: '2025-08-25',
            usuario: 'Usuário 1'
        }
    ];
    
    if (dados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-inbox fs-48 text-muted mb-2"></i>
                    <p class="text-muted mb-0">Nenhuma conferência encontrada</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = dados.map(item => `
        <tr>
            <td>${item.id}</td>
            <td>${item.nfe}</td>
            <td>${item.fornecedor}</td>
            <td>${item.produto}</td>
            <td>
                <span class="badge bg-success">${item.status}</span>
            </td>
            <td>
                <span class="badge bg-primary">${item.qualidade}</span>
            </td>
            <td>${item.data}</td>
            <td>${item.usuario}</td>
        </tr>
    `).join('');
}

function configurarFiltros() {
    // Configurar filtro de período
    const periodoFiltro = document.getElementById('periodo-filtro');
    if (periodoFiltro) {
        periodoFiltro.addEventListener('change', function() {
            if (this.value === 'custom') {
                // Implementar seleção de datas personalizadas
                alert('Funcionalidade de datas personalizadas será implementada em breve.');
                this.value = '30'; // Voltar para 30 dias
            }
        });
    }
}

function aplicarFiltros() {
    const periodo = document.getElementById('periodo-filtro').value;
    const status = document.getElementById('status-filtro').value;
    const fornecedor = document.getElementById('fornecedor-filtro').value;
    
    console.log('Aplicando filtros:', { periodo, status, fornecedor });
    
    // Aqui você implementaria a lógica de filtros
    // Por enquanto, apenas recarrega a tabela
    carregarTabela();
    
    // Mostrar mensagem de sucesso
    if (typeof showAlert === 'function') {
        showAlert('Filtros aplicados com sucesso!', 'success');
    }
}

function exportarRelatorio() {
    const periodo = document.getElementById('periodo-filtro').value;
    const status = document.getElementById('status-filtro').value;
    const fornecedor = document.getElementById('fornecedor-filtro').value;
    
    console.log('Exportando relatório com filtros:', { periodo, status, fornecedor });
    
    // Aqui você implementaria a exportação
    // Por enquanto, apenas mostra uma mensagem
    if (typeof showAlert === 'function') {
        showAlert('Funcionalidade de exportação será implementada em breve!', 'info');
    } else {
        alert('Funcionalidade de exportação será implementada em breve!');
    }
}
