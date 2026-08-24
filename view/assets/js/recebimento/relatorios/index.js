/**
 * Relatórios de recebimento: envia filtros para impressão ou exportação.
 */

document.addEventListener('DOMContentLoaded', function () {
    const formularios = {
        formRelatorioNF: {
            imprimir: '/recebimento/relatorios/imprimir/recebimento',
            exportar: '/recebimento/relatorios/exportar/recebimento'
        },
        formRelatorioConferencia: {
            imprimir: '/recebimento/relatorios/imprimir/conferencia',
            exportar: '/recebimento/relatorios/exportar/conferencia'
        },
        formRelatorioMovimentacao: {
            imprimir: '/recebimento/relatorios/imprimir/movimentacao',
            exportar: '/recebimento/relatorios/exportar/movimentacao'
        },
        formRelatorioEtiquetas: {
            imprimir: '/recebimento/relatorios/imprimir/etiquetas',
            exportar: '/recebimento/relatorios/exportar/etiquetas'
        },
        formRelatorioTransferencia: {
            imprimir: '/recebimento/relatorios/imprimir/transferencia',
            exportar: '/recebimento/relatorios/exportar/transferencia'
        }
    };

    Object.keys(formularios).forEach(function (formId) {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const params = new URLSearchParams();
            new FormData(form).forEach(function (valor, chave) {
                if (chave === 'formato' || valor === '') {
                    return;
                }
                params.append(chave, valor);
            });

            const formato = (form.querySelector('[name="formato"]') || {}).value || 'pdf';
            const destino = formato === 'excel' || formato === 'csv'
                ? formularios[formId].exportar
                : formularios[formId].imprimir;

            const query = params.toString();
            window.open(buildUrl(destino + (query ? '?' + query : '')), '_blank');
        });
    });
});
