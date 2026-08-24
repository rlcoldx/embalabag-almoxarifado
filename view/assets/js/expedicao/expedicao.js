(function () {
    function domain() {
        return window.DOMAIN || document.body.getAttribute('data-domain') || '';
    }

    function showMsg(ok, text) {
        const box = document.getElementById('expedicao-msg');
        if (!box) {
            window.alert(text);
            return;
        }
        box.classList.remove('d-none', 'alert-success', 'alert-danger');
        box.classList.add(ok ? 'alert-success' : 'alert-danger');
        box.textContent = text;
    }

    function postForm(url, data) {
        const body = new FormData();
        Object.keys(data).forEach(function (key) {
            body.append(key, data[key]);
        });
        return fetch(url, { method: 'POST', body: body }).then(function (res) {
            return res.json();
        });
    }

    document.querySelectorAll('[data-separar-item]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            postForm(domain() + '/expedicao/separacao/item', { item_id: btn.getAttribute('data-separar-item') })
                .then(function (json) {
                    if (json.success) {
                        window.location.reload();
                    } else {
                        showMsg(false, json.message || 'Não foi possível separar.');
                    }
                });
        });
    });

    document.querySelectorAll('[data-trocar-endereco]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const itemId = btn.getAttribute('data-trocar-endereco');
            const select = document.querySelector('[data-endereco-item="' + itemId + '"]');
            if (!select || !select.value) {
                showMsg(false, 'Escolha um endereço.');
                return;
            }
            postForm(domain() + '/expedicao/separacao/trocar-endereco', {
                item_id: itemId,
                armazenagem_id: select.value
            }).then(function (json) {
                showMsg(!!json.success, json.message || (json.success ? 'Endereço atualizado.' : 'Falha ao trocar.'));
                if (json.success) {
                    window.location.reload();
                }
            });
        });
    });

    const btnConferir = document.getElementById('btn-conferir');
    if (btnConferir) {
        btnConferir.addEventListener('click', function () {
            const codigo = (document.getElementById('conf-codigo').value || '').trim();
            if (!codigo) {
                showMsg(false, 'Informe o código.');
                return;
            }
            postForm(domain() + '/expedicao/conferencia/codigo', {
                pedido_id: btnConferir.getAttribute('data-pedido-id'),
                tipo: document.getElementById('conf-tipo').value,
                codigo: codigo
            }).then(function (json) {
                showMsg(!!json.success, json.message || '');
                if (json.success) {
                    window.location.reload();
                }
            });
        });
        document.getElementById('conf-codigo').addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                btnConferir.click();
            }
        });
    }

    const btnBipar = document.getElementById('btn-bipar');
    if (btnBipar) {
        btnBipar.addEventListener('click', function () {
            postForm(domain() + '/expedicao/bipar', {
                codigo_sige: document.getElementById('bipar-sige').value,
                etiqueta_cia: document.getElementById('bipar-cia').value,
                importar: document.getElementById('bipar-importar').checked ? '1' : ''
            }).then(function (json) {
                showMsg(!!json.success, json.message || '');
                const box = document.getElementById('bipar-resultado');
                const body = document.getElementById('bipar-resultado-body');
                if (json.success && json.pedido) {
                    box.classList.remove('d-none');
                    body.innerHTML = '<p class="mb-2"><strong>' + (json.pedido.codigo || '') + '</strong> · SIGE ' + (json.pedido.codigoSige || '-') + '</p>' +
                        '<a class="btn btn-primary btn-wave" href="' + json.redirect + '">Abrir pedido</a>';
                }
            });
        });
    }

    document.querySelectorAll('[data-salvar-encomenda]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = btn.closest('tr');
            const previsao = row.querySelector('[data-previsao]').value;
            postForm(domain() + '/expedicao/encomendas/salvar', {
                pedido_id: btn.getAttribute('data-pedido'),
                produto_id: btn.getAttribute('data-produto'),
                previsao_chegada: previsao
            }).then(function (json) {
                showMsg(!!json.success, json.message || '');
            });
        });
    });
})();
