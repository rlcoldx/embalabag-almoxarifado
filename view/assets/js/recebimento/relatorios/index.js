/**
 * Script para Relatórios de Recebimento
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar relatórios
    inicializarRelatorios();
    
    // Inicializar filtros
    inicializarFiltros();
});

/**
 * Inicializa os relatórios
 */
function inicializarRelatorios() {
    // Carregar relatório inicial
    carregarRelatorioRecebimento();
}

/**
 * Inicializa os filtros dos relatórios
 */
function inicializarFiltros() {
    // Filtros de data
    document.getElementById('dataInicio').addEventListener('change', atualizarRelatorios);
    document.getElementById('dataFim').addEventListener('change', atualizarRelatorios);
    
    // Filtros de tipo
    document.getElementById('tipoRelatorio').addEventListener('change', function() {
        const tipo = this.value;
        mostrarRelatorio(tipo);
    });
    
    // Botão exportar
    document.getElementById('btnExportar').addEventListener('click', exportarRelatorio);
    
    // Botão imprimir
    document.getElementById('btnImprimir').addEventListener('click', imprimirRelatorio);
}

/**
 * Carrega o relatório de recebimento
 */
function carregarRelatorioRecebimento() {
    const filtros = obterFiltros();
    
    fetch(DOMAIN + '/recebimento/relatorios/recebimento', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filtros)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            atualizarRelatorioRecebimento(data.relatorio);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar relatório:', error);
    });
}

/**
 * Atualiza o relatório de recebimento
 */
function atualizarRelatorioRecebimento(relatorio) {
    // Estatísticas gerais
    document.getElementById('totalNF').textContent = relatorio.total_nf || 0;
    document.getElementById('nfRecebidas').textContent = relatorio.nf_recebidas || 0;
    document.getElementById('nfPendentes').textContent = relatorio.nf_pendentes || 0;
    document.getElementById('valorTotal').textContent = 'R$ ' + (relatorio.valor_total || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Gráfico de recebimento por período
    atualizarGraficoRecebimento(relatorio.grafico_recebimento);
    
    // Tabela de fornecedores
    atualizarTabelaFornecedores(relatorio.fornecedores);
    
    // Tabela de produtos mais recebidos
    atualizarTabelaProdutos(relatorio.produtos_mais_recebidos);
}

/**
 * Atualiza gráfico de recebimento
 */
function atualizarGraficoRecebimento(dados) {
    const ctx = document.getElementById('graficoRecebimento').getContext('2d');
    
    // Destruir gráfico existente se houver
    if (window.graficoRecebimento) {
        window.graficoRecebimento.destroy();
    }
    
    window.graficoRecebimento = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.labels || [],
            datasets: [{
                label: 'Notas Fiscais Recebidas',
                data: dados.valores || [],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Recebimento por Período'
                }
            }
        }
    });
}

/**
 * Atualiza tabela de fornecedores
 */
function atualizarTabelaFornecedores(fornecedores) {
    const tbody = document.querySelector('#tabelaFornecedores tbody');
    tbody.innerHTML = '';
    
    fornecedores.forEach(fornecedor => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${fornecedor.nome}</td>
            <td>${fornecedor.total_nf}</td>
            <td>R$ ${parseFloat(fornecedor.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            <td>${fornecedor.percentual}%</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Atualiza tabela de produtos
 */
function atualizarTabelaProdutos(produtos) {
    const tbody = document.querySelector('#tabelaProdutos tbody');
    tbody.innerHTML = '';
    
    produtos.forEach(produto => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${produto.codigo}</td>
            <td>${produto.descricao}</td>
            <td>${produto.quantidade_total}</td>
            <td>${produto.total_nf}</td>
            <td>R$ ${parseFloat(produto.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Carrega relatório de conferência
 */
function carregarRelatorioConferencia() {
    const filtros = obterFiltros();
    
    fetch(DOMAIN + '/recebimento/relatorios/conferencia', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filtros)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            atualizarRelatorioConferencia(data.relatorio);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar relatório:', error);
    });
}

/**
 * Atualiza o relatório de conferência
 */
function atualizarRelatorioConferencia(relatorio) {
    // Estatísticas
    document.getElementById('totalConferencias').textContent = relatorio.total_conferencias || 0;
    document.getElementById('conferenciasAprovadas').textContent = relatorio.conferencias_aprovadas || 0;
    document.getElementById('conferenciasRejeitadas').textContent = relatorio.conferencias_rejeitadas || 0;
    document.getElementById('percentualAprovacao').textContent = (relatorio.percentual_aprovacao || 0) + '%';
    
    // Gráfico de qualidade
    atualizarGraficoQualidade(relatorio.grafico_qualidade);
    
    // Tabela de problemas encontrados
    atualizarTabelaProblemas(relatorio.problemas_encontrados);
}

/**
 * Atualiza gráfico de qualidade
 */
function atualizarGraficoQualidade(dados) {
    const ctx = document.getElementById('graficoQualidade').getContext('2d');
    
    if (window.graficoQualidade) {
        window.graficoQualidade.destroy();
    }
    
    window.graficoQualidade = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dados.labels || [],
            datasets: [{
                data: dados.valores || [],
                backgroundColor: [
                    '#28a745', // Excelente
                    '#20c997', // Bom
                    '#ffc107', // Regular
                    '#fd7e14', // Ruim
                    '#dc3545'  // Inutilizável
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Distribuição por Qualidade'
                }
            }
        }
    });
}

/**
 * Atualiza tabela de problemas
 */
function atualizarTabelaProblemas(problemas) {
    const tbody = document.querySelector('#tabelaProblemas tbody');
    tbody.innerHTML = '';
    
    problemas.forEach(problema => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${problema.tipo}</td>
            <td>${problema.quantidade}</td>
            <td>${problema.percentual}%</td>
            <td>${problema.fornecedor_mais_afetado}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Carrega relatório de movimentação
 */
function carregarRelatorioMovimentacao() {
    const filtros = obterFiltros();
    
    fetch(DOMAIN + '/recebimento/relatorios/movimentacao', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filtros)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            atualizarRelatorioMovimentacao(data.relatorio);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar relatório:', error);
    });
}

/**
 * Atualiza o relatório de movimentação
 */
function atualizarRelatorioMovimentacao(relatorio) {
    // Estatísticas
    document.getElementById('totalMovimentacoes').textContent = relatorio.total_movimentacoes || 0;
    document.getElementById('movimentacoesConcluidas').textContent = relatorio.movimentacoes_concluidas || 0;
    document.getElementById('movimentacoesPendentes').textContent = relatorio.movimentacoes_pendentes || 0;
    document.getElementById('tempoMedioExecucao').textContent = (relatorio.tempo_medio_execucao || 0) + ' min';
    
    // Gráfico de tipos de movimentação
    atualizarGraficoMovimentacao(relatorio.grafico_movimentacao);
    
    // Tabela de armazenagens mais utilizadas
    atualizarTabelaArmazenagens(relatorio.armazenagens_mais_utilizadas);
}

/**
 * Atualiza gráfico de movimentação
 */
function atualizarGraficoMovimentacao(dados) {
    const ctx = document.getElementById('graficoMovimentacao').getContext('2d');
    
    if (window.graficoMovimentacao) {
        window.graficoMovimentacao.destroy();
    }
    
    window.graficoMovimentacao = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dados.labels || [],
            datasets: [{
                label: 'Quantidade',
                data: dados.valores || [],
                backgroundColor: [
                    '#007bff', // Put-away
                    '#28a745', // Transferência
                    '#ffc107', // Reposição
                    '#6c757d'  // Ajuste
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Movimentações por Tipo'
                }
            }
        }
    });
}

/**
 * Atualiza tabela de armazenagens
 */
function atualizarTabelaArmazenagens(armazenagens) {
    const tbody = document.querySelector('#tabelaArmazenagens tbody');
    tbody.innerHTML = '';
    
    armazenagens.forEach(arm => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${arm.codigo}</td>
            <td>${arm.descricao}</td>
            <td>${arm.total_movimentacoes}</td>
            <td>${arm.ocupacao_atual}%</td>
            <td>${arm.produtos_diferentes}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Mostra o relatório selecionado
 */
function mostrarRelatorio(tipo) {
    // Ocultar todos os relatórios
    document.querySelectorAll('.relatorio-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Mostrar relatório selecionado
    const relatorioSection = document.getElementById('relatorio-' + tipo);
    if (relatorioSection) {
        relatorioSection.style.display = 'block';
        
        // Carregar dados do relatório
        switch (tipo) {
            case 'recebimento':
                carregarRelatorioRecebimento();
                break;
            case 'conferencia':
                carregarRelatorioConferencia();
                break;
            case 'movimentacao':
                carregarRelatorioMovimentacao();
                break;
        }
    }
}

/**
 * Atualiza todos os relatórios
 */
function atualizarRelatorios() {
    const tipoAtual = document.getElementById('tipoRelatorio').value;
    mostrarRelatorio(tipoAtual);
}

/**
 * Obtém os filtros aplicados
 */
function obterFiltros() {
    return {
        data_inicio: document.getElementById('dataInicio').value,
        data_fim: document.getElementById('dataFim').value,
        fornecedor: document.getElementById('filtroFornecedor').value,
        tipo_movimentacao: document.getElementById('filtroTipoMovimentacao').value
    };
}

/**
 * Exporta o relatório atual
 */
function exportarRelatorio() {
    const tipo = document.getElementById('tipoRelatorio').value;
    const filtros = obterFiltros();
    
    // Criar URL de exportação
    const params = new URLSearchParams({
        tipo: tipo,
        ...filtros
    });
    
    // Abrir nova janela para download
    window.open(DOMAIN + '/recebimento/relatorios/exportar?' + params.toString(), '_blank');
}

/**
 * Imprime o relatório atual
 */
function imprimirRelatorio() {
    const tipo = document.getElementById('tipoRelatorio').value;
    const relatorioSection = document.getElementById('relatorio-' + tipo);
    
    if (relatorioSection) {
        // Criar janela de impressão
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Relatório de ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</title>
                    <link rel="stylesheet" href="${DOMAIN}/view/assets/css/app.css">
                    <style>
                        @media print {
                            .no-print { display: none !important; }
                            body { margin: 0; padding: 20px; }
                        }
                    </style>
                </head>
                <body>
                    <h1>Relatório de ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</h1>
                    <p>Período: ${document.getElementById('dataInicio').value} a ${document.getElementById('dataFim').value}</p>
                    ${relatorioSection.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
} 