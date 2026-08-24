<?php

namespace Agencia\Close\Models\Produtos;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class Produtos extends Model
{

    public function getProdutos(): Read
    {
        $read = new Read();
        $read->ExeRead("produtos", "WHERE `status` <> 'Deletado' ORDER BY id DESC");
        return $read;
    }

    public function getProduto($id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM produtos WHERE id = :id ORDER BY id DESC LIMIT 1", "id={$id}");
        return $read;
    }

    public function getProdutoVariations($id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM produtos_variations WHERE id_produto = :id_produto ORDER BY date_create ASC", "id_produto={$id}");
        return $read;
    }

    public function getProdutoImages($id_produto): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM produtos_imagens WHERE id_produto = :id_produto ORDER BY `order`,`id` ASC", "id_produto={$id_produto}");
        return $read;
    }

    public function getProdutoPrecos($id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT pp.*, u.nome as empresa_nome FROM produtos_precos pp 
                        LEFT JOIN usuarios u ON u.id = pp.id_empresa 
                        WHERE pp.id_produto = :id_produto 
                        ORDER BY pp.date_create ASC", "id_produto={$id}");
        return $read;
    }

    public function getProdutoPrecoEmpresa($id_produto, $id_empresa): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM produtos_precos 
                        WHERE id_produto = :id_produto AND id_empresa = :id_empresa 
                        LIMIT 1", "id_produto={$id_produto}&id_empresa={$id_empresa}");
        return $read;
    }

    public function createDraft(array $params): Read
    {
        //SALVA O RASCUNHO
        $create = new Create();
        $params['status'] = 'Rascunho';
        $create->ExeCreate('produtos', $params);

        //RETORNA O ITEM SALVO
        $read = new Read();
        $read->FullRead("SELECT * FROM produtos ORDER BY id DESC LIMIT 1");
        $resultado = $read->getResult();
        if (!$resultado) {
            return $read;
        }
        $produto = $resultado[0];

        // SALVAR BLACKLIST DE EMPRESAS SE EXISTIR
        if (isset($params['empresas_blacklist'])) {
            $this->saveBlacklist($produto['id'], $params['empresas_blacklist']);
        }

        $this->registrarHistoricoProduto(function () use ($produto) {
            (new ProdutoHistorico())->registrarCriacao((int)$produto['id'], $produto);
        });

        return $read;
    }

    public function saveEdit(array $params): Update
    {
        //SALVA EDIÇÃO DO PRODUTO
        $update = new Update();
        $id = $params['id'];
        $anterior = $this->getProduto($id)->getResult()[0] ?? [];
        $relacionadosAlterados = isset($params['variavel'])
            || isset($params['preco_empresa'])
            || isset($params['categories_id']);

        if (isset($params['variavel']) && is_array($params['variavel']) && count($params['variavel']) > 0) {
            
            for ($i = 0; $i < count($params['variavel']); $i++) {

                if (($params['variavel'][$i]['cor'] != 'Selecione') && ($params['variavel'][$i]['cor'] != '')) {

                    // Verificar se é uma variação existente que teve a cor alterada
                    if (isset($params['variavel'][$i]['variacao_id_alterada']) && !empty($params['variavel'][$i]['variacao_id_alterada'])) {
                        $this->atualizarVariacaoExistente($params['variavel'][$i]['variacao_id_alterada'], $params['variavel'][$i]);
                    } else {
                        // Verificar se a variação já existe
                        $read = new Read();
                        $read->FullRead("SELECT id FROM `produtos_variations` WHERE id_produto = :id_produto AND cor = :cor", 
                            "id_produto={$id}&cor={$params['variavel'][$i]['cor']}");
                        
                        $variacao_existe = $read->getRowCount() > 0;

                    if (isset($params['variavel'][$i]['gerenciar_estoque'])) {
                        $gerenciar_estoque = 'yes';
                    } else {
                        $gerenciar_estoque = 'no';
                    }

                    if (isset($params['variavel'][$i]['estoque'])) {
                        $estoque = $params['variavel'][$i]['estoque'];
                    } else {
                        $estoque = 0;
                    }

                    if (isset($params['variavel'][$i]['encomenda'])) {
                        $encomenda = $params['variavel'][$i]['encomenda'];
                    } else {
                        $encomenda = 'no';
                    }

                    if (isset($params['variavel'][$i]['atraso'])) {
                        $atraso = $params['variavel'][$i]['atraso'];
                    } else {
                        $atraso = 0;
                    }

                    if ($variacao_existe) {
                        // Atualizar variação existente
                        $update_variacao = new Update();
                        $update_variacao->ExeUpdate("produtos_variations", [
                            'gerenciar_estoque' => $gerenciar_estoque,
                            'estoque' => $estoque,
                            'encomenda' => $encomenda,
                            'atraso' => $atraso,
                            'sku_fornecedor' => $params['variavel'][$i]['sku_fornecedor'],
                            'codigo_barras' => $params['variavel'][$i]['codigo_barras']
                        ], "WHERE id_produto = :id_produto AND cor = :cor", "id_produto={$id}&cor={$params['variavel'][$i]['cor']}");
                    } else {
                        // Inserir nova variação
                        $create = new Create();
                        $create->ExeCreate("produtos_variations", [
                            'id_produto' => $id,
                            'cor' => $params['variavel'][$i]['cor'],
                            'gerenciar_estoque' => $gerenciar_estoque,
                            'estoque' => $estoque,
                            'encomenda' => $encomenda,
                            'atraso' => $atraso,
                            'sku_fornecedor' => $params['variavel'][$i]['sku_fornecedor'],
                            'codigo_barras' => $params['variavel'][$i]['codigo_barras']
                        ]);
                    }
                    }
                }
            }
        }

        // SALVAR PREÇOS POR EMPRESA
        if (isset($params['preco_empresa']) && is_array($params['preco_empresa'])) {
            $deletar = new Read();
            $deletar->FullRead("DELETE FROM `produtos_precos` WHERE id_produto = :id_produto",
            "id_produto={$id}");

            for ($i = 0; $i < count($params['preco_empresa']); $i++) {

                if (($params['preco_empresa'][$i]['id_empresa'] != 'Selecione') && ($params['preco_empresa'][$i]['id_empresa'] != '') && ($params['preco_empresa'][$i]['preco'] != '')) {

                    $preco = $this->converterValoes($params['preco_empresa'][$i]['preco']);

                    $read = new Read();
                    $read->FullRead("INSERT INTO `produtos_precos` (`id_produto`, `id_empresa`, `preco`) 
                    VALUES ('" . $id . "', '" . $params['preco_empresa'][$i]['id_empresa'] . "', '" . $preco . "')");
                }
            }
        }

        if (isset($params['categories_id'])) {
            $dados_categorias = $this->saveCategory($id, $params['categories_id']);
            $params['categoria_id'] = $dados_categorias[0];
            $params['categoria'] = $dados_categorias[1];
        }

        // SALVAR BLACKLIST DE EMPRESAS
        if (isset($params['empresas_blacklist'])) {
            $this->saveBlacklist($id, $params['empresas_blacklist']);
        }

        unset($params['id']);
        unset($params['fileuploader-list-files']);
        unset($params['files']);
        unset($params['variavel']);
        unset($params['preco_empresa']);
        unset($params['categories_id']);
        unset($params['empresas_blacklist']);
        unset($params['variacao_id_alterada']);

        if (($params['valor'] != '') && ($params['valor'] != '0,00')) {
            $params['valor'] = $this->converterValoes($params['valor']);
        } else {
            $params['valor'] = '';
        }

        if (($params['promocao'] != '') && ($params['promocao'] != '0,00')) {
            $params['promocao'] = $this->converterValoes($params['promocao']);
        } else {
            $params['promocao'] = '';
        }

        $update->ExeUpdate('produtos', $params, 'WHERE `id` = :id', "id={$id}");
        $novo = $this->getProduto($id)->getResult()[0] ?? [];
        $this->registrarHistoricoProduto(function () use ($id, $anterior, $novo, $relacionadosAlterados) {
            (new ProdutoHistorico())->registrarAtualizacao((int)$id, $anterior, $novo, $relacionadosAlterados);
        });
        return $update;
    }

    /**
     * Atualizar variação existente (mudança de cor)
     */
    private function atualizarVariacaoExistente($variacao_id, $dados_variacao)
    {
        if (isset($dados_variacao['gerenciar_estoque'])) {
            $gerenciar_estoque = 'yes';
        } else {
            $gerenciar_estoque = 'no';
        }

        if (isset($dados_variacao['estoque'])) {
            $estoque = $dados_variacao['estoque'];
        } else {
            $estoque = 0;
        }

        if (isset($dados_variacao['encomenda'])) {
            $encomenda = $dados_variacao['encomenda'];
        } else {
            $encomenda = 'no';
        }

        if (isset($dados_variacao['atraso'])) {
            $atraso = $dados_variacao['atraso'];
        } else {
            $atraso = 0;
        }

        $update = new Update();
        $update->ExeUpdate("produtos_variations", [
            'cor' => $dados_variacao['cor'],
            'gerenciar_estoque' => $gerenciar_estoque,
            'estoque' => $estoque,
            'encomenda' => $encomenda,
            'atraso' => $atraso,
            'sku_fornecedor' => $dados_variacao['sku_fornecedor'],
            'codigo_barras' => $dados_variacao['codigo_barras']
        ], "WHERE id = :id", "id={$variacao_id}");
    }

    public function converterValoes($val)
    {
        $valorBR = str_replace('.', '', $val);
        $valorBR = str_replace(',', '.', $valorBR);
        $valorDecimal = floatval($valorBR);
        $valorFormatado = number_format($valorDecimal, 2, '.', '');
        return $valorFormatado;
    }

    public function excluirProduto($id_produto)
    {
        $read = new Read();
        $produtoAnterior = $this->getProduto($id_produto)->getResult()[0] ?? [];

        // Buscar todos os estoques do produto antes de deletar
        $read->FullRead("
            SELECT id, armazenagem_id, variacao_id, quantidade 
            FROM estoque 
            WHERE id_produto = :id_produto AND quantidade > 0 AND status = 'ativo'
        ", "id_produto={$id_produto}");
        $estoques = $read->getResult();
        
        // Obter ID do usuário logado
        $usuario_id = $_SESSION[BASE.'user_id'] ?? 1; // Default 1 se não houver sessão
        
        // Registrar movimentações de saída para cada estoque
        if ($estoques && is_array($estoques)) {
            foreach ($estoques as $estoque) {
                $create = new Create();
                $create->ExeCreate('movimentacoes_historico', [
                    'tipo' => 'saida',
                    'id_produto' => $id_produto,
                    'variacao_id' => $estoque['variacao_id'],
                    'quantidade' => $estoque['quantidade'],
                    'armazenagem_origem_id' => $estoque['armazenagem_id'],
                    'armazenagem_destino_id' => null,
                    'motivo' => 'Produto deletado',
                    'documento_referencia' => null,
                    'observacoes' => 'Estoque removido automaticamente ao deletar o produto',
                    'usuario_id' => $usuario_id,
                    'data_movimentacao' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Marcar produto como deletado
        $read->FullRead("UPDATE `produtos` SET `status` = 'Deletado' WHERE `id` = :id_produto", "id_produto={$id_produto}");
        
        // Marcar estoque como inativo para que não conte mais na capacidade das armazenagens
        $read->FullRead("UPDATE `estoque` SET `status` = 'inativo' WHERE `id_produto` = :id_produto", "id_produto={$id_produto}");

        if ($produtoAnterior) {
            $this->registrarHistoricoProduto(function () use ($id_produto, $produtoAnterior) {
                (new ProdutoHistorico())->registrarExclusao((int)$id_produto, $produtoAnterior);
            });
        }

        return true;
    }

    private function registrarHistoricoProduto(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            // O cadastro do produto não deve falhar se o histórico ainda não existir.
        }
    }

    public function saveCategory($id_produto, $categorias)
    {
        $read = new Read();
        $read->FullRead("DELETE FROM produtos_categorias WHERE `id_produto` = :id_produto", "id_produto={$id_produto}");

        $categorias_ids = '';
        $categorias_nomes = '';

        if (is_countable($categorias)) {
            for ($i = 0; $i < count($categorias); $i++) {

                $read = new Read();
                $read->FullRead("SELECT * FROM categorias WHERE `id` = :id LIMIT 1", "id={$categorias[$i]}");
                $categoria = $read->getResult()[0];

                $cat_insert = array('id_produto' => $id_produto, 'id_categoria' => $categoria['id'], 'nome' => $categoria['nome'], 'slug' => $categoria['slug'], 'nivel' => $categoria['nivel'], 'parent' => $categoria['parent']);
                $create = new Create();
                $create->ExeCreate('produtos_categorias', $cat_insert);

                $categorias_ids .= $categoria['id'] . ',';
                $categorias_nomes .= $categoria['nome'] . ',';
            }
        }

        $categorias_ids = substr($categorias_ids, 0, -1);
        $categorias_nomes = substr($categorias_nomes, 0, -1);

        return [$categorias_ids, $categorias_nomes];
        /*****/
    }

    public function getProdutoBlacklist($id_produto): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM produtos_blacklist WHERE id_produto = :id_produto", "id_produto={$id_produto}");
        return $read;
    }

    public function saveBlacklist($id_produto, $empresas_blacklist)
    {
        // Deletar blacklist existente
        $deletar = new Read();
        $deletar->FullRead("DELETE FROM `produtos_blacklist` WHERE id_produto = :id_produto", "id_produto={$id_produto}");

        // Inserir novas empresas na blacklist
        if (isset($empresas_blacklist) && is_array($empresas_blacklist)) {
            for ($i = 0; $i < count($empresas_blacklist); $i++) {
                if ($empresas_blacklist[$i] != '' && $empresas_blacklist[$i] != null) {
                    $read = new Read();
                    $read->FullRead("INSERT INTO `produtos_blacklist` (`id_produto`, `id_empresa`) 
                    VALUES ('" . $id_produto . "', '" . $empresas_blacklist[$i] . "')");
                }
            }
        }
    }

    // BUSCAR PRODUTOS COM ESTOQUE BAIXO
    public function getProdutosEstoqueBaixo(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT
                            p.id,
                            p.nome,
                            p.SKU,
                            COALESCE(p.SKU, CONCAT('#', p.id)) as codigo,
                            MIN(pv.estoque) as estoque_atual,
                            MIN(IF(pv.estoque_minimo > 0, pv.estoque_minimo, 1)) as estoque_minimo,
                            c.nome as categoria_nome
                        FROM produtos p
                        INNER JOIN produtos_variations pv ON pv.id_produto = p.id
                        LEFT JOIN categorias c ON c.id = p.categoria_id
                        WHERE p.status <> 'Deletado'
                          AND pv.estoque <= IF(pv.estoque_minimo > 0, pv.estoque_minimo, 1)
                        GROUP BY p.id, p.nome, p.SKU, c.nome
                        ORDER BY estoque_atual ASC, p.nome ASC");
        return $read;
    }

    // ADICIONAR ESTOQUE AO PRODUTO
    public function adicionarEstoque(array $params): bool
    {
        try {
            $id_produto = (int)($params['id_produto'] ?? $params['produto_id'] ?? 0);
            $quantidade = (int)($params['quantidade'] ?? 0);
            $tipo = $params['tipo'] ?? 'entrada';
            $observacao = $params['observacao'] ?? $params['observacoes'] ?? 'Entrada de estoque';
            $variacaoId = (int)($params['variacao_id'] ?? 0);

            if ($id_produto <= 0 || $quantidade <= 0) {
                return false;
            }

            $produto = new Read();
            if ($variacaoId > 0) {
                $produto->FullRead(
                    "SELECT id, estoque FROM produtos_variations WHERE id = :id AND id_produto = :id_produto LIMIT 1",
                    "id={$variacaoId}&id_produto={$id_produto}"
                );
            } else {
                $produto->FullRead(
                    "SELECT id, estoque FROM produtos_variations WHERE id_produto = :id_produto ORDER BY estoque ASC LIMIT 1",
                    "id_produto={$id_produto}"
                );
            }

            $variacao = $produto->getResult()[0] ?? null;
            if (!$variacao) {
                return false;
            }

            $estoque_atual = (int)$variacao['estoque'];
            $novo_estoque = $tipo === 'saida'
                ? max(0, $estoque_atual - $quantidade)
                : $estoque_atual + $quantidade;

            $update = new Update();
            $update->ExeUpdate(
                'produtos_variations',
                ['estoque' => $novo_estoque],
                'WHERE id = :id',
                "id={$variacao['id']}"
            );

            $create = new Create();
            $create->ExeCreate('movimentacoes_historico', [
                'tipo' => $tipo === 'saida' ? 'saida' : 'entrada',
                'id_produto' => $id_produto,
                'variacao_id' => $variacao['id'],
                'quantidade' => $quantidade,
                'armazenagem_origem_id' => null,
                'armazenagem_destino_id' => null,
                'motivo' => 'ajuste',
                'documento_referencia' => null,
                'observacoes' => $observacao,
                'usuario_id' => $_SESSION[BASE . 'user_id'] ?? 1,
                'data_movimentacao' => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}