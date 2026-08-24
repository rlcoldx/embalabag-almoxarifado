/**
 * Relatório de conferências
 */

let chartStatus = null;
let chartQualidade = null;

document.addEventListener('DOMContentLoaded', function () {
    carregarRelatorio();
    configurarFiltros();
});

function configurarFiltros() {
    const periodoFiltro = document.getElementById('periodo-filtro');
    if (periodoFiltro) {
        periodoFiltro.addEventListener('change', function () {
            if (this.value === 'custom') {
                Swal.fire('Aviso', 'Período personalizado será implementado em breve.', 'info');
                this.value = '30';
            }
        });
    }
}

function carregarRelatorio() {
    const periodo = document.getElementById('periodo-filtro')?.value || '30';
    const status = document.getElementById('status-filtro')?.value || '';
    const fornecedor = document.getElementById('fornecedor-filtro')?.value || '';

    const params = new URLSearchParams();
    if (periodo && periodo !== 'custom') {
        params.set('periodo', periodo);
    }
    if (status) {
        params.set('status', status);
    }
    if (fornecedor) {
        params.set('fornecedor', fornecedor);
    }

    fetch(buildUrl('/api/conferencia/relatorio?' + params.toString()), {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success) {
                Swal.fire('Erro', data.message || 'Não foi possível carregar o relatório.', 'error');
                return;
            }

            atualizarEstatisticas(data.estatisticas || {});
            renderizarTabela(data.dados || []);
            atualizarGraficos(data.graficos || {});
        })
        .catch(function (error) {
            console.error('Erro ao carregar relatório:', error);
            Swal.fire('Erro', 'Não foi possível carregar o relatório.', 'error');
        });
}

function atualizarEstatisticas(estatisticas) {
    document.getElementById('total-conferencias').textContent = estatisticas.total ?? 0;
    document.getElementById('conferencias-pendentes').textContent = estatisticas.pendentes ?? 0;
    document.getElementById('conferencias-concluidas').textContent = estatisticas.concluidas ?? 0;
    document.getElementById('conferencias-andamento').textContent = estatisticas.em_andamento ?? 0;
}

function renderizarTabela(dados) {
    const tbody = document.querySelector('#tabela-relatorio tbody');
    if (!tbody) {
        return;
    }

    if (!dados.length) {
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

    tbody.innerHTML = dados.map(function (item) {
        const statusBadge = getStatusBadge(item.status_conferencia);
        const qualidadeBadge = getQualidadeBadge(item.status_qualidade);
        const data = formatarData(item.data_conferencia);

        return `
            <tr>
                <td>${item.id}</td>
                <td>${item.numero_nfe}</td>
                <td>${item.fornecedor_nome}</td>
                <td>${item.produto_nome}</td>
                <td>${statusBadge}</td>
                <td>${qualidadeBadge}</td>
                <td>${data}</td>
                <td>${item.usuario_nome}</td>
            </tr>
        `;
    }).join('');
}

function atualizarGraficos(graficos) {
    if (typeof Chart === 'undefined') {
        return;
    }

    const status = graficos.status || {};
    const qualidade = graficos.qualidade || {};

    const ctxStatus = document.getElementById('chart-status');
    if (ctxStatus) {
        if (chartStatus) {
            chartStatus.destroy();
        }
        chartStatus = new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: ['Pendente', 'Em andamento', 'Concluída', 'Cancelada'],
                datasets: [{
                    data: [
                        status.pendente || 0,
                        status.em_andamento || 0,
                        status.concluida || 0,
                        status.cancelada || 0
                    ],
                    backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
                }]
            }
        });
    }

    const ctxQualidade = document.getElementById('chart-qualidade');
    if (ctxQualidade) {
        if (chartQualidade) {
            chartQualidade.destroy();
        }
        chartQualidade = new Chart(ctxQualidade, {
            type: 'bar',
            data: {
                labels: ['Aprovado', 'Reprovado', 'Pendente'],
                datasets: [{
                    label: 'Produtos',
                    data: [
                        qualidade.aprovado || 0,
                        qualidade.reprovado || 0,
                        qualidade.pendente || 0
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#6c757d']
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
}

function getStatusBadge(status) {
    const map = {
        pendente: 'warning',
        em_andamento: 'info',
        concluida: 'success',
        cancelada: 'danger'
    };
    const color = map[status] || 'secondary';
    const label = (status || 'pendente').replace('_', ' ');
    return `<span class="badge bg-${color}-transparent">${label}</span>`;
}

function getQualidadeBadge(qualidade) {
    const map = {
        aprovado: 'success',
        reprovado: 'danger',
        pendente: 'warning'
    };
    const color = map[qualidade] || 'secondary';
    return `<span class="badge bg-${color}-transparent">${qualidade || 'pendente'}</span>`;
}

function formatarData(data) {
    if (!data) {
        return '-';
    }
    const date = new Date(data);
    if (Number.isNaN(date.getTime())) {
        return data;
    }
    return date.toLocaleDateString('pt-BR');
}

function aplicarFiltros() {
    carregarRelatorio();
    if (typeof showAlert === 'function') {
        showAlert('Filtros aplicados com sucesso!', 'success');
    }
}

function exportarRelatorio() {
    const periodo = document.getElementById('periodo-filtro')?.value || '30';
    const status = document.getElementById('status-filtro')?.value || '';
    const fornecedor = document.getElementById('fornecedor-filtro')?.value || '';

    const params = new URLSearchParams();
    if (periodo && periodo !== 'custom') {
        params.set('periodo', periodo);
    }
    if (status) {
        params.set('status', status);
    }
    if (fornecedor) {
        params.set('fornecedor', fornecedor);
    }

    window.open(buildUrl('/recebimento/relatorios/exportar/conferencia?' + params.toString()), '_blank');
}
