<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;

/**
 * Controller para API de Movimentações de Estoque
 */
class MovimentacoesApiController extends Controller
{
    public function __construct($router)
    {
        parent::__construct($router);
        $this->setParams([]);
    }

    /**
     * Criar nova movimentação de estoque (entrada, saída ou movimentação)
     */
    public function criarMovimentacao($params)
    {
        try {
            // Verificar se é POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendErrorResponse('Método não permitido', 405);
                return;
            }

                         // Coletar dados do POST
             $dados = [
                'tipo' => $_POST['tipo'] ?? '',
                'id_produto' => $_POST['id_produto'] ?? '', // sku agora é id_produto
                'variacao_id' => $_POST['variacao_id'] ?? '',
                'quantidade' => (int)($_POST['quantidade'] ?? 0),
                'motivo' => $_POST['motivo'] ?? '',
                'documento_referencia' => $_POST['documento_referencia'] ?? '',
                'observacoes' => $_POST['observacoes'] ?? '',
                'data_movimentacao' => date('Y-m-d H:i:s'),
                'usuario_id' => $_SESSION['bagalmo_user_id'] ?? 1
             ];
            
            // Log simples para debug
            error_log("=== DADOS RECEBIDOS ===");
            error_log("POST: " . print_r($_POST, true));
            error_log("Dados processados: " . print_r($dados, true));

                         // Validar dados básicos
             if (empty($dados['tipo']) || empty($dados['id_produto']) || empty($dados['variacao_id']) || $dados['quantidade'] <= 0) {
                 $this->sendErrorResponse('Dados obrigatórios não fornecidos', 400);
                 return;
             }

            // Processar baseado no tipo
            switch ($dados['tipo']) {
                case 'entrada':
                    $this->processarEntrada($dados);
                    break;
                
                case 'saida':
                    $this->processarSaida($dados);
                    break;
                
                case 'movimentacao':
                    $this->processarMovimentacao($dados);
                    break;
                
                default:
                    $this->sendErrorResponse('Tipo de movimentação inválido', 400);
                    return;
            }

        } catch (\Exception $e) {
            error_log('Erro ao criar movimentação: ' . $e->getMessage());
            $this->sendErrorResponse('Erro interno do servidor', 500);
        }
    }

    /**
     * Processar entrada de estoque
     */
    private function processarEntrada($dados)
    {
        try {
            error_log("=== PROCESSAR ENTRADA ===");
            
            $armazenagemId = $_POST['armazenagem_id'] ?? '';
            error_log("Armazenagem ID: {$armazenagemId}");
            
            if (empty($armazenagemId)) {
                error_log("Armazenagem ID vazio");
                $this->sendErrorResponse('ID da armazenagem é obrigatório para entrada', 400);
                return;
            }

                         // Verificar se já existe estoque para este produto/variação nesta armazenagem
             $read = new Read();
             $query = "WHERE armazenagem_id = {$armazenagemId} AND id_produto = '{$dados['id_produto']}' AND variacao_id = '{$dados['variacao_id']}'";
            error_log("Query de busca: {$query}");
            
            $read->ExeRead("estoque", $query);
            $resultado = $read->getResult();
            error_log("Resultado da busca: " . print_r($resultado, true));
            
            if ($resultado) {
                // Atualizar estoque existente
                error_log("Atualizando estoque existente");
                $estoqueAtual = $resultado[0]['quantidade'] ?? 0;
                $novaQuantidade = $estoqueAtual + $dados['quantidade'];
                error_log("Estoque atual: {$estoqueAtual}, Nova quantidade: {$novaQuantidade}");
                
                                 $update = new Update();
                 $resultadoUpdate = $update->ExeUpdate("estoque", [
                     'quantidade' => $novaQuantidade,
                     'data_atualizacao' => $dados['data_movimentacao']
                 ],
                    "WHERE armazenagem_id = :armazenagem_id AND id_produto = :id_produto AND variacao_id = :variacao_id", 
                 "armazenagem_id={$armazenagemId}&id_produto={$dados['id_produto']}&variacao_id={$dados['variacao_id']}");
                
                error_log("Resultado do update: " . print_r($resultadoUpdate, true));
                
            } else {
                // Criar novo registro de estoque
                error_log("Criando novo registro de estoque");
                $create = new Create();
                                 $resultadoCreate = $create->ExeCreate("estoque", [
                     'armazenagem_id' => $armazenagemId,
                    'id_produto' => $dados['id_produto'],
                     'variacao_id' => $dados['variacao_id'],
                     'quantidade' => $dados['quantidade'],
                     'data_criacao' => $dados['data_movimentacao'],
                     'data_atualizacao' => $dados['data_movimentacao']
                 ]);
                
                error_log("Resultado do create: " . print_r($resultadoCreate, true));
            }

                         // Atualizar capacidade atual da armazenagem
             $this->atualizarCapacidadeArmazenagem($armazenagemId);
             
             // Registrar histórico da movimentação
             $this->registrarHistorico($dados, $armazenagemId, null);

             error_log("=== ENVIANDO RESPOSTA DE SUCESSO ===");
            $this->sendSuccessResponse('Entrada de estoque registrada com sucesso', [
                'tipo' => 'entrada',
                'quantidade' => $dados['quantidade'],
                'armazenagem_id' => $armazenagemId
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao processar entrada: ' . $e->getMessage());
            $this->sendErrorResponse('Erro ao processar entrada de estoque', 500);
        }
    }

    /**
     * Processar saída de estoque
     */
    private function processarSaida($dados)
    {
        try {
            $armazenagemId = $_POST['armazenagem_id'] ?? '';
            
            if (empty($armazenagemId)) {
                $this->sendErrorResponse('ID da armazenagem é obrigatório para saída', 400);
                return;
            }

                         // Verificar estoque disponível
             $read = new Read();
             $read->ExeRead("estoque", "WHERE armazenagem_id = {$armazenagemId} AND id_produto = '{$dados['id_produto']}' AND variacao_id = '{$dados['variacao_id']}'");
            
            if (!$read->getResult()) {
                $this->sendErrorResponse('Produto não encontrado no estoque desta armazenagem', 404);
                return;
            }

            $estoqueAtual = $read->getResult()[0]['quantidade'] ?? 0;
            
            if ($dados['quantidade'] > $estoqueAtual) {
                $this->sendErrorResponse('Quantidade solicitada excede o estoque disponível', 400);
                return;
            }

            // Atualizar estoque
            $novaQuantidade = $estoqueAtual - $dados['quantidade'];
            
            $update = new Update();
                         $update->ExeUpdate("estoque", [
                 'quantidade' => $novaQuantidade,
                 'data_atualizacao' => $dados['data_movimentacao']
             ], "WHERE armazenagem_id = :armazenagem_id AND id_produto = :id_produto AND variacao_id = :variacao_id",
             "armazenagem_id={$armazenagemId}&id_produto={$dados['id_produto']}&variacao_id={$dados['variacao_id']}");

                         // Atualizar capacidade atual da armazenagem
             $this->atualizarCapacidadeArmazenagem($armazenagemId);
             
             // Registrar histórico da movimentação
             $this->registrarHistorico($dados, $armazenagemId, null);

             $this->sendSuccessResponse('Saída de estoque registrada com sucesso', [
                'tipo' => 'saida',
                'quantidade' => $dados['quantidade'],
                'armazenagem_id' => $armazenagemId,
                'estoque_restante' => $novaQuantidade
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao processar saída: ' . $e->getMessage());
            $this->sendErrorResponse('Erro ao processar saída de estoque', 500);
        }
    }

    /**
     * Processar movimentação entre armazenagens
     */
    private function processarMovimentacao($dados)
    {
        try {
            $armazenagemOrigemId = $_POST['armazenagem_origem_id'] ?? '';
            $armazenagemDestinoId = $_POST['armazenagem_destino_id'] ?? '';
            
            if (empty($armazenagemOrigemId) || empty($armazenagemDestinoId)) {
                $this->sendErrorResponse('IDs das armazenagens de origem e destino são obrigatórios', 400);
                return;
            }

            if ($armazenagemOrigemId === $armazenagemDestinoId) {
                $this->sendErrorResponse('Armazenagem de origem deve ser diferente da armazenagem de destino', 400);
                return;
            }

                         // Verificar estoque na armazenagem de origem
             $read = new Read();
             $read->ExeRead("estoque", "WHERE armazenagem_id = {$armazenagemOrigemId} AND id_produto = '{$dados['id_produto']}' AND variacao_id = '{$dados['variacao_id']}'");
            
            if (!$read->getResult()) {
                $this->sendErrorResponse('Produto não encontrado no estoque da armazenagem de origem', 404);
                return;
            }

            $estoqueOrigem = $read->getResult()[0]['quantidade'] ?? 0;
            
            if ($dados['quantidade'] > $estoqueOrigem) {
                $this->sendErrorResponse('Quantidade solicitada excede o estoque disponível na armazenagem de origem', 400);
                return;
            }

            // Iniciar transação
            $create = new Create();
            
                         // Reduzir estoque da armazenagem de origem
             $novaQuantidadeOrigem = $estoqueOrigem - $dados['quantidade'];
             $update = new Update();
             $update->ExeUpdate("estoque", [
                 'quantidade' => $novaQuantidadeOrigem,
                 'data_atualizacao' => $dados['data_movimentacao']
             ], "WHERE armazenagem_id = :armazenagem_id AND id_produto = :id_produto AND variacao_id = :variacao_id",
             "armazenagem_id={$armazenagemOrigemId}&id_produto={$dados['id_produto']}&variacao_id={$dados['variacao_id']}");

             // Adicionar estoque na armazenagem de destino
             $read->ExeRead("estoque", "WHERE armazenagem_id = {$armazenagemDestinoId} AND id_produto = '{$dados['id_produto']}' AND variacao_id = '{$dados['variacao_id']}'");
            
            if ($read->getResult()) {
                // Atualizar estoque existente
                $estoqueDestino = $read->getResult()[0]['quantidade'] ?? 0;
                $novaQuantidadeDestino = $estoqueDestino + $dados['quantidade'];
                
                                 $update->ExeUpdate("estoque", [
                     'quantidade' => $novaQuantidadeDestino,
                     'data_atualizacao' => $dados['data_movimentacao']
                 ], "WHERE armazenagem_id = :armazenagem_id AND id_produto = :id_produto AND variacao_id = :variacao_id",
                 "armazenagem_id={$armazenagemDestinoId}&id_produto={$dados['id_produto']}&variacao_id={$dados['variacao_id']}");
                 
             } else {
                 // Criar novo registro de estoque
                 $create->ExeCreate("estoque", [
                     'armazenagem_id' => $armazenagemDestinoId,
                     'id_produto' => $dados['id_produto'],
                     'variacao_id' => $dados['variacao_id'],
                     'quantidade' => $dados['quantidade'],
                     'data_criacao' => $dados['data_movimentacao'],
                     'data_atualizacao' => $dados['data_movimentacao']
                 ]);
             }

                         // Atualizar capacidade das armazenagens
             $this->atualizarCapacidadeArmazenagem($armazenagemOrigemId);
             $this->atualizarCapacidadeArmazenagem($armazenagemDestinoId);
             
             // Registrar histórico da movimentação
             $this->registrarHistorico($dados, $armazenagemOrigemId, $armazenagemDestinoId);

             $this->sendSuccessResponse('Movimentação de estoque registrada com sucesso', [
                'tipo' => 'movimentacao',
                'quantidade' => $dados['quantidade'],
                'armazenagem_origem_id' => $armazenagemOrigemId,
                'armazenagem_destino_id' => $armazenagemDestinoId
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao processar movimentação: ' . $e->getMessage());
            $this->sendErrorResponse('Erro ao processar movimentação de estoque', 500);
        }
    }

    /**
     * Registrar histórico da movimentação
     */
    private function registrarHistorico($dados, $armazenagemOrigemId, $armazenagemDestinoId)
    {
        try {
            error_log("=== REGISTRAR HISTORICO ===");
            error_log("Dados para histórico: " . print_r($dados, true));
            error_log("Armazenagem Origem: {$armazenagemOrigemId}");
            error_log("Armazenagem Destino: {$armazenagemDestinoId}");
            
            $create = new Create();
                         $resultadoHistorico = $create->ExeCreate("movimentacoes_historico", [
                 'tipo' => $dados['tipo'],
                 'id_produto' => $dados['id_produto'],
                 'variacao_id' => $dados['variacao_id'],
                 'quantidade' => $dados['quantidade'],
                 'armazenagem_origem_id' => $armazenagemOrigemId,
                 'armazenagem_destino_id' => $armazenagemDestinoId,
                 'motivo' => $dados['motivo'],
                 'documento_referencia' => $dados['documento_referencia'],
                 'observacoes' => $dados['observacoes'],
                 'usuario_id' => $dados['usuario_id'],
                 'data_movimentacao' => $dados['data_movimentacao']
             ]);
            
            error_log("Resultado do histórico: " . print_r($resultadoHistorico, true));
            error_log("Histórico registrado com sucesso");
            
        } catch (\Exception $e) {
            error_log('Erro ao registrar histórico: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Obter movimentações de uma armazenagem específica
     */
    public function getMovimentacoesArmazenagem($params)
    {
        try {
            $armazenagemId = $params['id'] ?? '';
            
            if (empty($armazenagemId)) {
                $this->sendErrorResponse('ID da armazenagem é obrigatório', 400);
                return;
            }

            $read = new Read();
            $read->ExeRead("movimentacoes_historico", "WHERE armazenagem_origem_id = {$armazenagemId} OR armazenagem_destino_id = {$armazenagemId} ORDER BY data_movimentacao DESC");

            $movimentacoes = $read->getResult() ?? [];

            $this->sendSuccessResponse('Movimentações carregadas com sucesso', $movimentacoes);

        } catch (\Exception $e) {
            error_log('Erro ao buscar movimentações: ' . $e->getMessage());
            $this->sendErrorResponse('Erro ao buscar movimentações', 500);
        }
    }

    /**
     * Enviar resposta de sucesso
     */
    private function sendSuccessResponse($message, $data = null)
    {
        header('Content-Type: application/json');
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        echo json_encode($response);
    }

         /**
      * Atualizar capacidade atual da armazenagem
      */
     private function atualizarCapacidadeArmazenagem($armazenagemId)
     {
         try {
             $read = new Read();
             $read->FullRead("
                 SELECT COALESCE(SUM(quantidade), 0) as total_quantidade
                 FROM estoque 
                 WHERE armazenagem_id = :armazenagem_id
             ", "armazenagem_id={$armazenagemId}");
             
             $result = $read->getResult();
             $totalQuantidade = $result[0]['total_quantidade'] ?? 0;
             
             $update = new Update();
             $update->ExeUpdate("armazenagens", 
                 ['capacidade_atual' => $totalQuantidade], 
                 "WHERE id = :id", 
                 "id={$armazenagemId}"
             );
             
         } catch (\Exception $e) {
             error_log('Erro ao atualizar capacidade da armazenagem: ' . $e->getMessage());
         }
     }
 
     /**
      * Enviar resposta de erro
      */
     private function sendErrorResponse($message, $code = 400)
     {
         http_response_code($code);
         header('Content-Type: application/json');
         $response = [
             'success' => false,
             'error' => $message
         ];
         
         echo json_encode($response);
     }
 }
