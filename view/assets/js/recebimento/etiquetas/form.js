/**
 * Formulário compartilhado de etiquetas (create/edit)
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEtiqueta');
    if (!form) {
        return;
    }

    const tipoEtiqueta = document.getElementById('tipoEtiqueta');
    const codigoEtiqueta = document.getElementById('codigoEtiqueta');
    const referenciaTipo = document.getElementById('referenciaTipo');
    const referenciaId = document.getElementById('referenciaId');
    const conteudoEtiqueta = document.getElementById('conteudoEtiqueta');

    if (tipoEtiqueta && codigoEtiqueta && !codigoEtiqueta.value) {
        tipoEtiqueta.addEventListener('change', gerarCodigoAutomatico);
    }

    if (referenciaTipo && referenciaId) {
        referenciaTipo.addEventListener('change', filtrarReferencias);
        filtrarReferencias();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!validarFormulario()) {
            return;
        }

        const formData = new FormData(form);

        Swal.fire({
            title: 'Salvando...',
            text: 'Aguarde enquanto salvamos a etiqueta.',
            allowOutsideClick: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message || 'Etiqueta salva com sucesso.',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        window.location.href = data.redirect || buildUrl('/recebimento/etiquetas');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message || data.error || 'Erro ao salvar etiqueta.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao enviar formulário.',
                    confirmButtonText: 'OK'
                });
            });
    });

    function gerarCodigoAutomatico() {
        const tipo = tipoEtiqueta.value;
        if (!tipo || codigoEtiqueta.value) {
            return;
        }

        const prefixos = {
            localizacao: 'LOC',
            palete: 'PAL',
            caixa: 'CAI',
            produto: 'PRO',
            armazenagem: 'ARM'
        };
        const prefixo = prefixos[tipo] || 'ETQ';
        const timestamp = Date.now().toString().slice(-6);
        codigoEtiqueta.value = prefixo + timestamp;
    }

    function filtrarReferencias() {
        const tipo = referenciaTipo.value;
        Array.from(referenciaId.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = Boolean(tipo) && option.dataset.tipo !== tipo;
        });

        const selecionada = referenciaId.options[referenciaId.selectedIndex];
        if (selecionada && selecionada.hidden) {
            referenciaId.value = '';
        }
    }

    function validarFormulario() {
        const tipo = tipoEtiqueta ? tipoEtiqueta.value : '';
        const conteudo = conteudoEtiqueta ? conteudoEtiqueta.value.trim() : '';

        if (!tipo) {
            Swal.fire('Erro!', 'Selecione o tipo de etiqueta.', 'error');
            return false;
        }

        if (!conteudo) {
            Swal.fire('Erro!', 'O conteúdo da etiqueta é obrigatório.', 'error');
            return false;
        }

        return true;
    }
});
