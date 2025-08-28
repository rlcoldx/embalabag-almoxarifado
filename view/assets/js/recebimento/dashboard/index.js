/**
 * Script para Dashboard de Recebimento
 */

document.addEventListener('DOMContentLoaded', function() {
    const DOMAIN = document.body.getAttribute('data-domain') || '';
    
    // Inicializar dashboard
    inicializarDashboard();
    
    // Atualizar dados a cada 5 minutos
    setInterval(atualizarDados, 300000);
});

/**
 * Inicializa o dashboard
 */
function inicializarDashboard() {
    carregarEstatisticas();
    carregarGraficos();
    carregarTabelas();
}

/**
 * Carrega as estatísticas principais
 */
function carregarEstatisticas() {
    fetch(DOMAIN + '/recebimento/dashboard/estatisticas', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            atualizarCardsEstatisticas(data.estatisticas);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar estatísticas:', error);
    });
}

/**
 * Atualiza os cards de estatísticas
 */
function atualizarCardsEstatisticas(estatisticas) {
    // Notas Fiscais Pendentes
    document.getElementById('nfPendentes').textContent = estatisticas.nf_pendentes || 0;
    
    // Notas Fiscais Recebidas (hoje)
    document.getElementById('nfRecebidasHoje').textContent = estatisticas.nf_recebidas_hoje || 0;
    
    // Conferências Pendentes
    document.getElementById('conferenciasPendentes').textContent = estatisticas.conferencias_pendentes || 0;
    
    // Movimentações Pendentes
    document.getElementById('movimentacoesPendentes').textContent = estatisticas.movimentacoes_pendentes || 0;
    
    // Valor Total Recebido (mês)
    const valorTotal = parseFloat(estatisticas.valor_total_mes || 0);
    document.getElementById('valorTotalMes').textContent = 'R$ ' + valorTotal.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Carrega os gráficos
 */
function carregarGraficos() {
    carregarGraficoNotasFiscais();
    carregarGraficoConferencias();
    carregarGraficoMovimentacoes();
}

/**
 * Carrega gráfico de notas fiscais por status
 */
function carregarGraficoNotasFiscais() {
    fetch(DOMAIN + '/recebimento/dashboard/grafico-nf', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const ctx = document.getElementById('graficoNotasFiscais').getContext('2d');
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            '#dc3545', // Pendente
                            '#28a745', // Recebida
                            '#ffc107', // Em Conferência
                            '#17a2b8'  // Conferida
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
                            text: 'Notas Fiscais por Status'
                        }
                    }
                }
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar gráfico NF:', error);
    });
}

/**
 * Carrega gráfico de conferências por qualidade
 */
function carregarGraficoConferencias() {
    fetch(DOMAIN + '/recebimento/dashboard/grafico-conferencias', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const ctx = document.getElementById('graficoConferencias').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Quantidade',
                        data: data.values,
                        backgroundColor: [
                            '#28a745', // Excelente
                            '#20c997', // Bom
                            '#ffc107', // Regular
                            '#fd7e14', // Ruim
                            '#dc3545'  // Inutilizável
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
                            text: 'Conferências por Qualidade'
                        }
                    }
                }
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar gráfico conferências:', error);
    });
}

/**
 * Carrega gráfico de movimentações por tipo
 */
function carregarGraficoMovimentacoes() {
    fetch(DOMAIN + '/recebimento/dashboard/grafico-movimentacoes', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const ctx = document.getElementById('graficoMovimentacoes').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Put-away',
                        data: data.put_away,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Transferência',
                        data: data.transferencia,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Reposição',
                        data: data.reposicao,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
                            text: 'Movimentações por Tipo (Últimos 7 dias)'
                        }
                    }
                }
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar gráfico movimentações:', error);
    });
}

/**
 * Carrega as tabelas de dados
 */
function carregarTabelas() {
    carregarTabelaNotasFiscaisRecentes();
    carregarTabelaConferenciasRecentes();
    carregarTabelaMovimentacoesRecentes();
}

/**
 * Carrega tabela de notas fiscais recentes
 */
function carregarTabelaNotasFiscaisRecentes() {
    fetch(DOMAIN + '/recebimento/dashboard/nf-recentes', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#tabelaNFRecentes tbody');
            tbody.innerHTML = '';
            
            data.notas_fiscais.forEach(nf => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${nf.numero}</td>
                    <td>${nf.fornecedor}</td>
                    <td>${nf.data_emissao}</td>
                    <td><span class="badge bg-${getStatusColor(nf.status)}">${nf.status}</span></td>
                    <td>R$ ${parseFloat(nf.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar NF recentes:', error);
    });
}

/**
 * Carrega tabela de conferências recentes
 */
function carregarTabelaConferenciasRecentes() {
    fetch(DOMAIN + '/recebimento/dashboard/conferencias-recentes', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#tabelaConferenciasRecentes tbody');
            tbody.innerHTML = '';
            
            data.conferencias.forEach(conf => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${conf.produto}</td>
                    <td>${conf.numero_nf}</td>
                    <td>${conf.quantidade_recebida}/${conf.quantidade_esperada}</td>
                    <td><span class="badge bg-${getQualidadeColor(conf.status_qualidade)}">${conf.status_qualidade}</span></td>
                    <td>${conf.data_conferencia}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar conferências recentes:', error);
    });
}

/**
 * Carrega tabela de movimentações recentes
 */
function carregarTabelaMovimentacoesRecentes() {
    fetch(DOMAIN + '/recebimento/dashboard/movimentacoes-recentes', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#tabelaMovimentacoesRecentes tbody');
            tbody.innerHTML = '';
            
            data.movimentacoes.forEach(mov => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${mov.produto}</td>
                    <td>${mov.tipo_movimentacao}</td>
                    <td>${mov.armazenagem_destino}</td>
                    <td>${mov.quantidade_movimentada}</td>
                    <td><span class="badge bg-${getStatusColor(mov.status)}">${mov.status}</span></td>
                    <td>${mov.data_movimentacao}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar movimentações recentes:', error);
    });
}

/**
 * Atualiza todos os dados do dashboard
 */
function atualizarDados() {
    carregarEstatisticas();
    carregarTabelas();
}

/**
 * Retorna a cor do badge baseada no status
 */
function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case 'pendente':
            return 'warning';
        case 'recebida':
        case 'concluida':
            return 'success';
        case 'em_andamento':
            return 'info';
        case 'cancelada':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Retorna a cor do badge baseada na qualidade
 */
function getQualidadeColor(qualidade) {
    switch (qualidade.toLowerCase()) {
        case 'excelente':
            return 'success';
        case 'bom':
            return 'info';
        case 'regular':
            return 'warning';
        case 'ruim':
            return 'danger';
        case 'inutilizavel':
            return 'dark';
        default:
            return 'secondary';
    }
} 