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
        $produto = $read->getResult()[0];

        // SALVAR BLACKLIST DE EMPRESAS SE EXISTIR
        if (isset($params['empresas_blacklist'])) {
            $this->saveBlacklist($produto['id'], $params['empresas_blacklist']);
        }

        return $read;
    }

    public function saveEdit(array $params): Update
    {
        //SALVA EDIÇÃO DO PRODUTO
        $update = new Update();
        $id = $params['id'];

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
        $read->FullRead("UPDATE `produtos` SET `status` = 'Deletado' WHERE `id` = :id_produto", "id_produto={$id_produto}");
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
        $read->FullRead("SELECT p.*, c.nome as categoria_nome 
                        FROM produtos p 
                        LEFT JOIN categorias c ON c.id = p.categoria_id 
                        WHERE p.status <> 'Deletado' 
                        AND p.estoque_atual <= p.estoque_minimo 
                        AND p.estoque_minimo > 0 
                        ORDER BY p.estoque_atual ASC, p.nome ASC");
        return $read;
    }

    // ADICIONAR ESTOQUE AO PRODUTO
    public function adicionarEstoque(array $params): bool
    {
        try {
            $id_produto = $params['id_produto'];
            $quantidade = (int)$params['quantidade'];
            $tipo = $params['tipo'] ?? 'entrada'; // entrada ou saida
            $observacao = $params['observacao'] ?? '';

            // Buscar produto atual
            $produto = new Read();
            $produto->FullRead("SELECT estoque_atual FROM produtos WHERE id = :id", "id={$id_produto}");
            $produto_atual = $produto->getResult();

            if (empty($produto_atual)) {
                return false;
            }

            $estoque_atual = (int)$produto_atual[0]['estoque_atual'];
            
            // Calcular novo estoque
            if ($tipo === 'entrada') {
                $novo_estoque = $estoque_atual + $quantidade;
            } else {
                $novo_estoque = $estoque_atual - $quantidade;
                if ($novo_estoque < 0) {
                    $novo_estoque = 0;
                }
            }

            // Atualizar estoque do produto
            $update = new Update();
            $update->ExeUpdate('produtos', ['estoque_atual' => $novo_estoque], 'WHERE id = :id', "id={$id_produto}");

            // Registrar movimentação de estoque
            $movimentacao = [
                'id_produto' => $id_produto,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'estoque_anterior' => $estoque_atual,
                'estoque_atual' => $novo_estoque,
                'observacao' => $observacao,
                'data_movimentacao' => date('Y-m-d H:i:s')
            ];

            $create = new Create();
            $create->ExeCreate('produtos_movimentacoes', $movimentacao);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}