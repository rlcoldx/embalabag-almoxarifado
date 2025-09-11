<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Helpers\Result;

class ProdutosApiController extends Controller
{
    public function __construct($router)
    {
        parent::__construct($router);
        $this->setParams([]);
    }

    /**
     * Buscar produtos por termo de pesquisa
     */
    public function buscar($params)
    {
        try {
            $search = $_GET['search'] ?? '';
            $read = new Read();

            if (!empty($search)) {
                $read->ExeRead("produtos", "WHERE (SKU LIKE '%{$search}%' OR nome LIKE '%{$search}%' OR categoria LIKE '%{$search}%') AND status <> 'Deletado' ORDER BY nome ASC");
            } else {
                $read->ExeRead("produtos", "WHERE status <> 'Deletado' ORDER BY nome ASC");
            }

            $produtos = $read->getResult();
            
            // Contar total
            if (!empty($search)) {
                $read->ExeRead("produtos", "WHERE (SKU LIKE '%{$search}%' OR nome LIKE '%{$search}%' OR categoria LIKE '%{$search}%') AND status <> 'Deletado'");
            } else {
                $read->ExeRead("produtos", "WHERE status <> 'Deletado'");
            }
            
            $total = $read->getResult()[0]['total'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $produtos,
                'total' => $total
            ];

            header('Content-Type: application/json');
            echo json_encode($response);

        } catch (\Exception $e) {
            $this->sendErrorResponse('Erro ao buscar produtos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar variações de um produto
     */
    public function variacoes($params)
    {
        try {
            $produtoId = $params['id'] ?? null;
            
            if (!$produtoId) {
                $this->sendErrorResponse('ID do produto não informado');
                return;
            }

            $read = new Read();
            $read->ExeRead("produtos_variations", "WHERE id_produto = {$produtoId} ORDER BY cor ASC");

            $variacoes = $read->getResult();
            
            // ✅ CORRIGIDO: Adicionar ID único para cada variação
            if ($variacoes) {
                foreach ($variacoes as $key => $variacao) {
                    // Criar um ID único baseado no id_produto + cor + índice
                    $variacoes[$key]['id'] = $variacao['id'];
                    $variacoes[$key]['id_produto'] = $variacao['id_produto'];
                }
            }
            
            $response = [
                'success' => true,
                'data' => $variacoes
            ];

            header('Content-Type: application/json');
            echo json_encode($response);

        } catch (\Exception $e) {
            $this->sendErrorResponse('Erro ao buscar variações: ' . $e->getMessage());
        }
    }

    /**
     * Buscar produto por SKU
     */
    public function porSku($params)
    {
        try {
            $sku = $params['sku'] ?? null;
            
            if (!$sku) {
                $this->sendErrorResponse('SKU não informado');
                return;
            }

            $read = new Read();
            $read->ExeRead("produtos", "WHERE SKU = '{$sku}' AND status <> 'Deletado'");

            $produto = $read->getResult();
            
            if (empty($produto)) {
                $this->sendErrorResponse('Produto não encontrado');
                return;
            }

            $response = [
                'success' => true,
                'data' => $produto[0]
            ];

            header('Content-Type: application/json');
            echo json_encode($response);

        } catch (\Exception $e) {
            $this->sendErrorResponse('Erro ao buscar produto: ' . $e->getMessage());
        }
    }

    /**
     * Deletar variação de produto
     */
    public function deletarVariacao($params)
    {
        try {
            $variacaoId = $params['id'] ?? null;
            
            if (!$variacaoId) {
                $this->sendErrorResponse('ID da variação não informado');
                return;
            }

            // Verificar se a variação existe
            $read = new Read();
            $read->ExeRead("produtos_variations", "WHERE id = {$variacaoId}");
            
            if (!$read->getResult()) {
                $this->sendErrorResponse('Variação não encontrada');
                return;
            }

            // Deletar a variação
            $delete = new Delete();
            $delete->ExeDelete("produtos_variations", "WHERE id = :id", "id={$variacaoId}");
            
            $response = [
                'success' => true,
                'message' => 'Variação deletada com sucesso'
            ];

            header('Content-Type: application/json');
            echo json_encode($response);

        } catch (\Exception $e) {
            $this->sendErrorResponse('Erro ao deletar variação: ' . $e->getMessage());
        }
    }

    /**
     * Enviar resposta de erro
     */
    private function sendErrorResponse($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
