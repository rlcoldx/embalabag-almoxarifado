/**
 * JavaScript para o Dashboard da Home
 * Funcionalidades interativas e melhorias de UX
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Atualizar estatísticas em tempo real (a cada 5 minutos)
    setInterval(function() {
        updateDashboardStats();
    }, 300000); // 5 minutos
    
    // Adicionar efeitos de hover nos cards
    initializeCardHoverEffects();
    
    // Inicializar tooltips
    initializeTooltips();
    
    // Adicionar animações de contagem
    initializeCounters();
    
    // Configurar auto-refresh opcional
    setupAutoRefresh();
});

/**
 * Atualizar estatísticas do dashboard via AJAX
 */
function updateDashboardStats() {
    // Aqui você pode implementar uma chamada AJAX para atualizar as estatísticas
    // Por enquanto, apenas mostra um indicador visual
    console.log('Atualizando estatísticas do dashboard...');
}

/**
 * Inicializar efeitos de hover nos cards
 */
function initializeCardHoverEffects() {
    const cards = document.querySelectorAll('.custom-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';
        });
    });
}

/**
 * Inicializar tooltips do Bootstrap
 */
function initializeTooltips() {
    // Verificar se o Bootstrap está disponível
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Inicializar contadores animados
 */
function initializeCounters() {
    // Lista de IDs dos contadores que devem ser animados
    const counterIds = [
        'contador-produtos-total',
        'contador-armazenagens-total',
        'contador-estoque-valor',
        'contador-usuarios-total',
        'contador-produtos-ativos',
        'contador-produtos-rascunho',
        'contador-produtos-estoque-baixo',
        'contador-produtos-sem-estoque',
        'contador-armazenagens-capacidade-total',
        'contador-armazenagens-capacidade-utilizada'
    ];
    
    counterIds.forEach(id => {
        const counter = document.getElementById(id);
        if (counter) {
            const text = counter.textContent;
            const number = parseFloat(text.replace(/[^\d.,]/g, '').replace(',', '.'));
            
            if (!isNaN(number)) {
                animateCounter(counter, 0, number, 2000);
            }
        }
    });
}

/**
 * Animar contador de um valor inicial até o final
 */
function animateCounter(element, start, end, duration) {
    const startTime = performance.now();
    const startValue = start;
    const change = end - start;
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Função de easing (suave)
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentValue = startValue + (change * easeOutQuart);
        
        // Formatar o número baseado no tipo
        if (end % 1 === 0) {
            element.textContent = Math.floor(currentValue).toLocaleString('pt-BR');
        } else {
            element.textContent = currentValue.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

/**
 * Configurar auto-refresh opcional
 */
function setupAutoRefresh() {
    const refreshButton = document.querySelector('a[onclick="window.location.reload()"]');
    
    if (refreshButton) {
        // Adicionar indicador de última atualização
        const lastUpdate = document.createElement('small');
        lastUpdate.className = 'text-white ms-2';
        lastUpdate.textContent = 'Última atualização: ' + new Date().toLocaleTimeString('pt-BR');
        
        refreshButton.parentNode.appendChild(lastUpdate);
        
        // Atualizar timestamp quando clicar no botão
        refreshButton.addEventListener('click', function() {
            setTimeout(() => {
                lastUpdate.textContent = 'Última atualização: ' + new Date().toLocaleTimeString('pt-BR');
            }, 1000);
        });
    }
}

/**
 * Função para exportar dados do dashboard (futuro)
 */
function exportDashboardData() {
    // Implementar exportação de dados do dashboard
    console.log('Exportando dados do dashboard...');
    
    // Aqui você pode implementar a exportação para PDF, Excel, etc.
    alert('Funcionalidade de exportação será implementada em breve!');
}

/**
 * Função para filtrar dados por período
 */
function filterDashboardByPeriod(period) {
    console.log('Filtrando dashboard por período:', period);
    
    // Implementar filtros por período (hoje, semana, mês, ano)
    // Esta função pode ser expandida para fazer chamadas AJAX
    // e atualizar as estatísticas dinamicamente
}

/**
 * Função para mostrar/esconder seções específicas
 */
function toggleDashboardSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * Função para adicionar notificações toast
 */
function showNotification(message, type = 'info') {
    // Verificar se o SweetAlert2 está disponível
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Dashboard',
            text: message,
            icon: type,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback para alert simples
        alert(message);
    }
}

// Event listeners para eventos de conectividade
window.addEventListener('online', function() {
    showNotification('Sistema online novamente', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Sistema offline', 'warning');
});

/**
 * Função para adicionar indicadores de carregamento
 */
function showLoadingIndicator(element) {
    if (element) {
        element.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Carregando...</p></div>';
    }
}

/**
 * Função para remover indicadores de carregamento
 */
function hideLoadingIndicator(element, originalContent) {
    if (element && originalContent) {
        element.innerHTML = originalContent;
    }
}

// Exportar funções para uso global
window.DashboardUtils = {
    updateStats: updateDashboardStats,
    exportData: exportDashboardData,
    filterByPeriod: filterDashboardByPeriod,
    toggleSection: toggleDashboardSection,
    showNotification: showNotification,
    showLoading: showLoadingIndicator,
    hideLoading: hideLoadingIndicator
};
