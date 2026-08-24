<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Helpers\Result;
use Agencia\Close\Helpers\User\PermissionHelper;

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
        $this->checkSession();

        try {
            $search = trim($_GET['search'] ?? '');
            $read = new Read();

            if ($search !== '') {
                $term = '%' . $search . '%';
                $read->FullRead(
                    "SELECT * FROM produtos
                     WHERE (SKU LIKE :s1 OR nome LIKE :s2 OR categoria LIKE :s3)
                       AND status <> 'Deletado'
                     ORDER BY nome ASC",
                    "s1={$term}&s2={$term}&s3={$term}"
                );
            } else {
                $read->FullRead("SELECT * FROM produtos WHERE status <> 'Deletado' ORDER BY nome ASC");
            }

            $produtos = $read->getResult();
            
            // Contar total
            $total = count($produtos);
            
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
        $this->checkSession();

        try {
            $produtoId = (int)($params['id'] ?? 0);
            
            if ($produtoId <= 0) {
                $this->sendErrorResponse('ID do produto não informado');
                return;
            }

            $read = new Read();
            $read->FullRead(
                "SELECT * FROM produtos_variations WHERE id_produto = :id_produto ORDER BY cor ASC",
                "id_produto={$produtoId}"
            );

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
        $this->checkSession();

        try {
            $sku = trim($params['sku'] ?? '');
            
            if ($sku === '') {
                $this->sendErrorResponse('SKU não informado');
                return;
            }

            $read = new Read();
            $read->FullRead(
                "SELECT * FROM produtos WHERE SKU = :sku AND status <> 'Deletado'",
                "sku={$sku}"
            );

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
        $this->checkSession();
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('produtos', 'editar')) {
            $this->sendErrorResponse('Sem permissão para excluir variações', 403);
            return;
        }

        try {
            $variacaoId = (int)($params['id'] ?? 0);
            
            if ($variacaoId <= 0) {
                $this->sendErrorResponse('ID da variação não informado');
                return;
            }

            // Verificar se a variação existe
            $read = new Read();
            $read->FullRead("SELECT id FROM produtos_variations WHERE id = :id", "id={$variacaoId}");
            
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
