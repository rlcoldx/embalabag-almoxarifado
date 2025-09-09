/**
 * DataTable - Sistema de Tabelas Dinâmicas
 * Gerencia paginação, pesquisa, filtros e ordenação via Ajax
 */

// Variáveis globais para armazenar estado das tabelas
const dataTableState = {};

// Definir DOMAIN a partir do atributo data-domain do body
var DOMAIN = document.body.getAttribute('data-domain') || '';

/**
 * Inicializa uma DataTable
 */
function initDataTable(tableName, dataUrl) {
    // Carregar configurações salvas
    const savedSettings = loadDataTableSettings(tableName);
    
    // Inicializar estado com configurações salvas
    dataTableState[tableName] = {
        data: [],
        pagination: {},
        filters: savedSettings.filters || {},
        columns: {},
        searchable_columns: [],
        orderable_columns: [],
        limit_options: [10, 25, 50, 100],
        current_page: 1,
        current_limit: savedSettings.limit || 10,
        current_search: '',
        current_order_by: savedSettings.orderBy || 'id',
        current_order_dir: savedSettings.orderDir || 'DESC',
        dataUrl: dataUrl
    };

    // Configurar controles com valores salvos
    const limitSelect = document.getElementById(`limit-${tableName}`);
    if (limitSelect) {
        limitSelect.value = dataTableState[tableName].current_limit;
    }

    const orderBySelect = document.getElementById(`order-by-${tableName}`);
    if (orderBySelect) {
        orderBySelect.value = dataTableState[tableName].current_order_by;
    }

    const orderDirBtn = document.getElementById(`order-dir-${tableName}`);
    if (orderDirBtn) {
        const icon = orderDirBtn.querySelector('i');
        if (icon) {
            icon.className = dataTableState[tableName].current_order_dir === 'ASC' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
        }
    }

    // Carregar dados iniciais
    loadData(tableName);
}

/**
 * Função para salvar configurações atuais
 */
function saveCurrentSettings(tableName) {
    const state = dataTableState[tableName];
    if (!state) return;
    
    const settings = {
        limit: state.current_limit,
        orderBy: state.current_order_by,
        orderDir: state.current_order_dir,
        filters: state.filters
    };
    
    saveDataTableSettings(tableName, settings);
}

/**
 * Carrega dados da tabela via Ajax
 */
function loadData(tableName) {
    const state = dataTableState[tableName];
    if (state.loading) return;

    // Salvar configurações atuais
    saveCurrentSettings(tableName);

    state.loading = true;
    showLoading(tableName);

    // Construir URL com parâmetros
    const params = new URLSearchParams({
        page: state.current_page,
        limit: state.current_limit,
        search: state.current_search,
        order_by: state.current_order_by,
        order_dir: state.current_order_dir
    });

    // Adicionar filtros
    Object.keys(state.filters).forEach(key => {
        if (state.filters[key] !== '' && state.filters[key] !== null) {
            if (typeof state.filters[key] === 'object') {
                // Para filtros de data range
                Object.keys(state.filters[key]).forEach(subKey => {
                    if (state.filters[key][subKey]) {
                        params.append(`filters[${key}][${subKey}]`, state.filters[key][subKey]);
                    }
                });
            } else {
                params.append(`filters[${key}]`, state.filters[key]);
            }
        }
    });

    const url = `${state.dataUrl}?${params.toString()}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success !== false) {
            renderTable(tableName, data);
        } else {
            showError(tableName, data.error || 'Erro ao carregar dados');
        }
    })
    .catch(error => {
        showError(tableName, 'Erro de conexão');
    })
    .finally(() => {
        state.loading = false;
        hideLoading(tableName);
    });
}

/**
 * Renderiza a tabela com os dados recebidos
 */
function renderTable(tableName, data) {    
    const state = dataTableState[tableName];
    
    // Atualizar estado PRIMEIRO
    state.columns = data.columns;
    state.filters = data.filters;
    state.orderable_columns = data.orderable_columns;
    
    // Renderizar cabeçalhos
    renderHeaders(tableName, data.columns);
    
    // Renderizar dados
    renderData(tableName, data.data);
    
    // Renderizar paginação
    renderPagination(tableName, data.pagination);
    
    // Renderizar filtros
    renderFilters(tableName, data.filters);
    
    // Renderizar controles de ordenação
    renderOrderControls(tableName, data.orderable_columns);
    
    // Atualizar informações
    updateInfo(tableName, data.pagination);
}

/**
 * Renderiza os cabeçalhos da tabela
 */
function renderHeaders(tableName, columns) {
    const headerRow = document.getElementById(`header-${tableName}`);
    if (!headerRow) return;

    headerRow.innerHTML = '';
        
    Object.keys(columns).forEach(columnName => {
        const column = columns[columnName];
        const th = document.createElement('th');
        th.className = 'text-nowrap';
        
        if (column.type === 'actions') {
            th.innerHTML = '<i class="fas fa-cog"></i>';
            th.className += ' text-center';
            th.style.width = '100px';
        } else {
            th.textContent = column.label;
            
            // Adicionar ordenação se a coluna for ordenável
            if (isOrderable(tableName, columnName)) {
                th.style.cursor = 'pointer';
                th.onclick = () => changeOrderBy(tableName, columnName);
                th.innerHTML += ' <i class="fas fa-sort ms-1"></i>';
            }
        }
        
        headerRow.appendChild(th);
    });
}

/**
 * Renderiza os dados da tabela
 */
function renderData(tableName, data) {
    const tbody = document.getElementById(`body-${tableName}`);
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (!data || data.length === 0) {
        const template = document.getElementById(`empty-row-${tableName}`);
        if (template) {
            tbody.innerHTML = template.innerHTML;
        }
        return;
    }

    const state = dataTableState[tableName];
    const columns = state.columns || {};
    
    data.forEach((row, index) => {
        const tr = document.createElement('tr');
        
        Object.keys(columns).forEach(columnName => {
            const column = columns[columnName];
            const td = document.createElement('td');
                        
            switch (column.type) {
                case 'number':
                    td.textContent = row[columnName] || '0';
                    td.className = 'text-end';
                    break;
                    
                case 'select':
                    const options = column.options?.options || {};
                    const value = row[columnName];
                    
                    // Configuração específica para tipo de usuário
                    if (columnName === 'tipo' && tableName === 'users') {
                        const tipoMap = {
                            '1': { text: 'Administrador', class: 'bg-primary' },
                            '2': { text: 'Funcionário', class: 'bg-info' },
                            '3': { text: 'Companhia', class: 'bg-warning' }
                        };
                        
                        if (tipoMap[value]) {
                            td.innerHTML = `<span class="badge ${tipoMap[value].class}">${tipoMap[value].text}</span>`;
                        } else {
                            td.textContent = options[value] || value || '-';
                        }
                    } else {
                        td.textContent = options[value] || value || '-';
                    }
                    break;
                    
                case 'datetime':
                    td.textContent = formatDateTime(row[columnName]);
                    break;
                    
                case 'date':
                    td.textContent = formatDate(row[columnName]);
                    break;
                    
                case 'actions':
                    td.innerHTML = renderActions(tableName, row, column);
                    td.className = 'text-center';
                    break;
                    
                case 'status':
                    td.innerHTML = renderStatus(row[columnName]);
                    break;
                    
                case 'avatar':
                    td.innerHTML = renderAvatar(row[columnName], row);
                    break;
                    
                default:
                    // Configuração específica para nome de usuário
                    if (columnName === 'nome' && tableName === 'users') {
                        const initials = (row[columnName] || 'U').substring(0, 2).toUpperCase();
                        td.innerHTML = `
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-xs avatar-rounded me-2 bg-primary-transparent fw-semibold">
                                    ${initials}
                                </span>
                                <span class="fw-semibold">${row[columnName] || '-'}</span>
                            </div>
                        `;
                    } else {
                        td.textContent = row[columnName] || '-';
                    }
            }
            
            tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
    });
}

/**
 * Renderiza as ações da linha
 */
function renderActions(tableName, row, column) {

    let DOMAIN = document.body.getAttribute('data-domain') || '';

    const actions = column.options?.actions || [];
    let html = '<div class="btn-list">';
    
    actions.forEach(action => {
        const url = action.url.replace(':id', row.id);
        const icon = action.icon || 'fas fa-edit';
        const color = action.color || 'outline-primary';
        
        html += `<a href="${url}" class="btn btn-sm btn-${color}" title="${action.label}">
                    <i class="${icon}"></i>
                 </a>`;
    });
    
    // Ações padrão se não especificadas
    if (actions.length === 0) {
        html += `<a href="${buildUrl(`/${tableName}/edit/${row.id}`)}" class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                 </a>`;
        html += `<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem('${tableName}', ${row.id})" title="Excluir">
                    <i class="fas fa-trash"></i>
                 </button>`;
    }
    
    html += '</div>';
    return html;
}

/**
 * Renderiza status com badge
 */
function renderStatus(status) {
    const statusMap = {
        'ativo': 'success',
        'inativo': 'danger',
        'bloqueado': 'warning',
        'pendente': 'warning',
        'aprovado': 'success',
        'rejeitado': 'danger'
    };
    
    const color = statusMap[status] || 'secondary';
    return `<span class="badge bg-${color}">${status}</span>`;
}

/**
 * Renderiza avatar
 */
function renderAvatar(avatar, row) {
    if (avatar) {
        return `<img src="${avatar}" class="avatar avatar-sm avatar-rounded" alt="${row.nome}">`;
    }
    
    // Avatar com iniciais
    const name = row.nome || row.email || 'U';
    const initials = name.substring(0, 2).toUpperCase();
    return `<span class="avatar avatar-sm avatar-rounded bg-primary-transparent fw-semibold">${initials}</span>`;
}

/**
 * Renderiza a paginação
 */
function renderPagination(tableName, pagination) {
    const paginationContainer = document.getElementById(`pagination-${tableName}`);
    const paginationInfo = document.getElementById(`pagination-info-${tableName}`);
    
    if (!paginationContainer || !paginationInfo) return;

    if (!pagination || typeof pagination !== 'object') {
        paginationInfo.textContent = 'Página 1 de 1';
        paginationContainer.innerHTML = '';
        return;
    }

    // Informações da paginação
    paginationInfo.textContent = `Página ${pagination.current_page} de ${pagination.total_pages}`;

    // Botões de paginação
    let html = '';
    
    // Botão anterior
    html += `<li class="page-item ${!pagination.has_prev ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage('${tableName}', ${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
             </li>`;

    // Páginas numeradas
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage('${tableName}', ${i})">${i}</a>
                 </li>`;
    }

    // Botão próximo
    html += `<li class="page-item ${!pagination.has_next ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage('${tableName}', ${pagination.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
             </li>`;

    paginationContainer.innerHTML = html;
}

/**
 * Renderiza os filtros
 */
function renderFilters(tableName, filters) {
    const filtersContainer = document.getElementById(`filters-${tableName}`);
    if (!filtersContainer) return;

    filtersContainer.innerHTML = '';
    
    if (!filters || typeof filters !== 'object') {
        return;
    }
    
    Object.keys(filters).forEach(filterName => {
        const filter = filters[filterName];
        const col = document.createElement('div');
        col.className = 'col-md-3 col-sm-6 mb-2';
        
        let html = `<label class="form-label">${filter.label}</label>`;
        
        switch (filter.type) {
            case 'select':
                html += `<select class="form-select form-select-sm" id="filter-${tableName}-${filterName}" onchange="applyFilter('${tableName}', '${filterName}')">
                            <option value="">Todos</option>`;
                const options = filter.options?.options || {};
                Object.keys(options).forEach(value => {
                    html += `<option value="${value}">${options[value]}</option>`;
                });
                html += '</select>';
                break;
                
            case 'date_range':
                html += `<div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="filter-${tableName}-${filterName}-start" onchange="applyDateFilter('${tableName}', '${filterName}')">
                            <span class="input-group-text">até</span>
                            <input type="date" class="form-control" id="filter-${tableName}-${filterName}-end" onchange="applyDateFilter('${tableName}', '${filterName}')">
                         </div>`;
                break;
                
            default:
                html += `<input type="text" class="form-control form-control-sm" id="filter-${tableName}-${filterName}" placeholder="${filter.label}" onchange="applyFilter('${tableName}', '${filterName}')">`;
        }
        
        col.innerHTML = html;
        filtersContainer.appendChild(col);
    });
}

/**
 * Renderiza controles de ordenação
 */
function renderOrderControls(tableName, orderableColumns) {
    const state = dataTableState[tableName];
    const orderBySelect = document.getElementById(`order-by-${tableName}`);
    if (!orderBySelect) return;

    orderBySelect.innerHTML = '';
    if (!orderableColumns) return;

    orderableColumns.forEach(column => {
        const option = document.createElement('option');
        option.value = column;
        option.textContent = state.columns[column]?.label || column;
        if (column === state.current_order_by) {
            option.selected = true;
        }
        orderBySelect.appendChild(option);
    });
}

/**
 * Atualiza informações da tabela
 */
function updateInfo(tableName, pagination) {
    const infoElement = document.getElementById(`info-${tableName}`);
    if (!infoElement) return;

    if (!pagination || typeof pagination !== 'object') {
        infoElement.textContent = 'Mostrando 0 de 0 registros';
        return;
    }

    infoElement.textContent = `Mostrando ${pagination.from} a ${pagination.to} de ${pagination.total} registros`;
}

/**
 * Funções de controle
 */
function goToPage(tableName, page) {
    if (page < 1) return;
    
    dataTableState[tableName].current_page = page;
    loadData(tableName);
}

function changeLimit(tableName) {
    const limit = parseInt(document.getElementById(`limit-${tableName}`).value);
    dataTableState[tableName].current_limit = limit;
    dataTableState[tableName].current_page = 1;
    loadData(tableName);
}

function searchData(tableName) {
    const search = document.getElementById(`search-${tableName}`).value;
    dataTableState[tableName].current_search = search;
    dataTableState[tableName].current_page = 1;
    loadData(tableName);
}

function changeOrder(tableName) {
    const orderBy = document.getElementById(`order-by-${tableName}`).value;
    dataTableState[tableName].current_order_by = orderBy;
    dataTableState[tableName].current_page = 1;
    loadData(tableName);
}

function changeOrderBy(tableName, columnName) {
    const state = dataTableState[tableName];
    
    if (state.current_order_by === columnName) {
        state.current_order_dir = state.current_order_dir === 'ASC' ? 'DESC' : 'ASC';
    } else {
        state.current_order_by = columnName;
        state.current_order_dir = 'ASC';
    }
    
    loadData(tableName);
}

function toggleOrderDirection(tableName) {
    const state = dataTableState[tableName];
    state.current_order_dir = state.current_order_dir === 'ASC' ? 'DESC' : 'ASC';
    
    // Atualizar o ícone do botão
    const orderDirBtn = document.getElementById(`order-dir-${tableName}`);
    if (orderDirBtn) {
        const icon = orderDirBtn.querySelector('i');
        if (icon) {
            icon.className = state.current_order_dir === 'ASC' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
        }
    }
    
    loadData(tableName);
}

function applyFilter(tableName, filterName) {
    const value = document.getElementById(`filter-${tableName}-${filterName}`).value;
    dataTableState[tableName].filters[filterName] = value;
    dataTableState[tableName].current_page = 1;
    loadData(tableName);
}

function applyDateFilter(tableName, filterName) {
    const start = document.getElementById(`filter-${tableName}-${filterName}-start`).value;
    const end = document.getElementById(`filter-${tableName}-${filterName}-end`).value;
    
    dataTableState[tableName].filters[filterName] = { start, end };
    dataTableState[tableName].current_page = 1;
    loadData(tableName);
}

function clearFilters(tableName) {
    dataTableState[tableName].filters = {};
    dataTableState[tableName].current_page = 1;
    
    // Limpar campos de filtro
    const filtersContainer = document.getElementById(`filters-${tableName}`);
    if (filtersContainer) {
        filtersContainer.querySelectorAll('input, select').forEach(input => {
            input.value = '';
        });
    }
    
    loadData(tableName);
}

function refreshData(tableName) {
    loadData(tableName);
}

function exportData(tableName) {
    const state = dataTableState[tableName];
    const params = new URLSearchParams({
        export: 'true',
        search: state.current_search,
        order_by: state.current_order_by,
        order_dir: state.current_order_dir
    });

    // Adicionar filtros
    Object.keys(state.filters).forEach(key => {
        if (state.filters[key] !== '' && state.filters[key] !== null) {
            params.append(`filters[${key}]`, state.filters[key]);
        }
    });

    const url = `${state.dataUrl}?${params.toString()}`;
    window.open(url, '_blank');
}

function deleteItem(tableName, id) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(buildUrl(`/${tableName}/delete/${id}`), {
                method: 'POST',
                data: {id: id},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message || 'Item excluído com sucesso',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        loadData(tableName);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.error || 'Erro ao excluir item'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de conexão'
                });
            });
        }
    });
}

/**
 * Funções auxiliares
 */
function showLoading(tableName) {
    const tbody = document.getElementById(`body-${tableName}`);
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="100" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></td></tr>';
    }
}

function hideLoading(tableName) {
    // Loading é removido quando os dados são renderizados
}

function showError(tableName, message) {
    const tbody = document.getElementById(`body-${tableName}`);
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="100" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fs-48 mb-2"></i><p>${message}</p></td></tr>`;
    }
}

function showSuccess(message) {
    // Implementar notificação de sucesso
}

function isOrderable(tableName, columnName) {
    const state = dataTableState[tableName];
    return state.orderable_columns && state.orderable_columns.includes(columnName);
}

function getColumnLabel(tableName, columnName) {
    const state = dataTableState[tableName];
    return state.columns && state.columns[columnName] ? state.columns[columnName].label : columnName;
}

function formatDateTime(dateTime) {
    if (!dateTime) return '-';
    return new Date(dateTime).toLocaleString('pt-BR');
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-BR');
}

// Função para exportar dados para CSV
function exportToCSV(tableName) {
    const state = dataTableState[tableName];
    // Construir parâmetros da URL
    const params = new URLSearchParams();
    
    // Adicionar parâmetros atuais
    params.append('page', state.current_page);
    params.append('limit', state.current_limit);
    params.append('search', state.current_search);
    params.append('order_by', state.current_order_by);
    params.append('order_dir', state.current_order_dir);
    
    // Adicionar filtros
    Object.keys(state.filters).forEach(key => {
        const filter = state.filters[key];
        if (filter && filter !== '') {
            if (typeof filter === 'object') {
                // Para filtros de data range
                if (filter.start) params.append(`filters[${key}][start]`, filter.start);
                if (filter.end) params.append(`filters[${key}][end]`, filter.end);
            } else {
                params.append(`filters[${key}]`, filter);
            }
        }
    });
    
    // Determinar URL de exportação baseada na URL atual
    let exportUrl = '';
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/cargos')) {
        exportUrl = buildUrl(`/export/cargos?${params.toString()}`);
    } else if (currentPath.includes('/users') || currentPath.includes('/usuarios')) {
        exportUrl = buildUrl(`/export/users?${params.toString()}`);
    } else if (currentPath.includes('/permissoes')) {
        exportUrl = buildUrl(`/export/permissoes?${params.toString()}`);
    } else if (currentPath.includes('/log-acessos') || currentPath.includes('/log_acessos')) {
        exportUrl = buildUrl(`/export/log-acessos?${params.toString()}`);
    }
    
    if (exportUrl) {
        // Abrir em nova aba para download
        window.open(exportUrl, '_blank');
    } else {
        alert('Funcionalidade de exportação não disponível para esta página');
    }
}

// Funções para gerenciar cookies
function setCookie(name, value, days = 365) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${encodeURIComponent(JSON.stringify(value))};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) {
            try {
                return JSON.parse(decodeURIComponent(c.substring(nameEQ.length, c.length)));
            } catch (e) {
                return null;
            }
        }
    }
    return null;
}

// Função para salvar configurações do DataTable
function saveDataTableSettings(tableName, settings) {
    const cookieName = `datatable_${tableName}_settings`;
    setCookie(cookieName, settings);
}

// Função para carregar configurações do DataTable
function loadDataTableSettings(tableName) {
    const cookieName = `datatable_${tableName}_settings`;
    const settings = getCookie(cookieName);
    
    if (settings) {
        return settings;
    }
    
    // Configurações padrão
    return {
        limit: 10,
        orderBy: 'id',
        orderDir: 'DESC',
        filters: {}
    };
} 