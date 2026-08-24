/**
 * Formulário de pedidos (create/edit)
 */

function initPedidoForm(options) {
    const itens = [];
    let produtoSelecionado = null;

    const buscaInput = document.getElementById('buscaProduto');
    const resultadosDiv = document.getElementById('resultadosProduto');
    const btnAdicionar = document.getElementById('btnAdicionarItem');
    const itemQty = document.getElementById('itemQty');
    const itemValor = document.getElementById('itemValor');
    const itensBody = document.getElementById('itensBody');
    const itensJson = document.getElementById('itensJson');
    const totalPedido = document.getElementById('totalPedido');
    const form = document.getElementById('formPedido');

    if (options.initialItens && options.initialItens.length) {
        options.initialItens.forEach(function(item) {
            itens.push({
                id_produto: parseInt(item.id_produto, 10),
                nome: item.produto_nome || 'Produto',
                sku: item.produto_sku || '-',
                qty: parseInt(item.qty, 10) || 1,
                valor_unidade: parseFloat(item.valor_unidade) || 0,
            });
        });
        renderItens();
    }

    if (buscaInput) {
        buscaInput.addEventListener('input', function() {
            const termo = this.value.trim();
            if (termo.length < 2) {
                resultadosDiv.innerHTML = '';
                return;
            }

            fetch(buildUrl('/api/produtos/buscar?search=' + encodeURIComponent(termo)))
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    resultadosDiv.innerHTML = '';
                    if (!data.success || !data.data || !data.data.length) {
                        return;
                    }

                    data.data.slice(0, 10).forEach(function(produto) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.textContent = (produto.SKU || '') + ' - ' + (produto.nome || '');
                        btn.addEventListener('click', function() {
                            produtoSelecionado = produto;
                            buscaInput.value = produto.nome;
                            resultadosDiv.innerHTML = '';
                            btnAdicionar.disabled = false;

                            const preco = parseFloat(produto.preco || produto.valor || 0);
                            if (preco > 0) {
                                itemValor.value = preco.toFixed(2);
                            }
                        });
                        resultadosDiv.appendChild(btn);
                    });
                });
        });
    }

    if (btnAdicionar) {
        btnAdicionar.addEventListener('click', function() {
            if (!produtoSelecionado) {
                return;
            }

            const qty = parseInt(itemQty.value, 10) || 1;
            const valor = parseFloat(itemValor.value) || 0;
            const existente = itens.find(function(i) { return i.id_produto === produtoSelecionado.id; });

            if (existente) {
                existente.qty += qty;
                existente.valor_unidade = valor;
            } else {
                itens.push({
                    id_produto: parseInt(produtoSelecionado.id, 10),
                    nome: produtoSelecionado.nome,
                    sku: produtoSelecionado.SKU || '-',
                    qty: qty,
                    valor_unidade: valor,
                });
            }

            produtoSelecionado = null;
            buscaInput.value = '';
            itemQty.value = 1;
            itemValor.value = '0';
            btnAdicionar.disabled = true;
            renderItens();
        });
    }

    function renderItens() {
        itensBody.innerHTML = '';

        if (!itens.length) {
            itensBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Nenhum item adicionado</td></tr>';
            atualizarTotal();
            return;
        }

        itens.forEach(function(item, index) {
            const total = item.qty * item.valor_unidade;
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(item.nome) + '</td>' +
                '<td>' + escapeHtml(item.sku) + '</td>' +
                '<td class="text-center">' + item.qty + '</td>' +
                '<td class="text-end">R$ ' + formatMoney(item.valor_unidade) + '</td>' +
                '<td class="text-end">R$ ' + formatMoney(total) + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-index="' + index + '">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</td>';

            tr.querySelector('button').addEventListener('click', function() {
                itens.splice(index, 1);
                renderItens();
            });

            itensBody.appendChild(tr);
        });

        atualizarTotal();
    }

    function atualizarTotal() {
        const total = itens.reduce(function(sum, item) {
            return sum + (item.qty * item.valor_unidade);
        }, 0);

        totalPedido.textContent = 'R$ ' + formatMoney(total);
        itensJson.value = JSON.stringify(itens.map(function(item) {
            return {
                id_produto: item.id_produto,
                qty: item.qty,
                valor_unidade: item.valor_unidade,
            };
        }));
    }

    function formatMoney(value) {
        return parseFloat(value || 0).toFixed(2).replace('.', ',');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!itens.length) {
                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Adicione pelo menos um item ao pedido.' });
                return;
            }

            const formData = new FormData(form);
            const urlEncoded = new URLSearchParams(formData).toString();

            fetch(options.storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: urlEncoded,
            })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: data.message || 'Pedido salvo com sucesso.',
                            confirmButtonText: 'OK',
                        }).then(function() {
                            window.location.href = data.redirect || buildUrl('/pedidos');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.error || 'Erro ao salvar pedido.',
                        });
                    }
                })
                .catch(function() {
                    Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha na comunicação com o servidor.' });
                });
        });
    }
}
