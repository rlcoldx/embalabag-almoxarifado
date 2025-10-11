<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;

class TransferenciasApiController extends Controller
{
    public function criar()
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('transferencias', 'criar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            return $this->jsonResponse(['success' => false, 'message' => 'Dados inválidos']);
        }

        $requiredFields = ['id_produto', 'variacao_id', 'quantidade', 'armazenagem_origem_id', 'armazenagem_destino_id'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                return $this->jsonResponse(['success' => false, 'message' => "Campo {$field} é obrigatório"]);
            }
        }

        try {
            // Verificar se a variação existe e tem estoque suficiente na origem
            $read = new Read();
            $read->FullRead("
                SELECT e.*, p.SKU, p.nome, pv.cor
                FROM estoque e
                INNER JOIN produtos p ON e.id_produto = p.id
                INNER JOIN produtos_variations pv ON e.variacao_id = pv.id
                WHERE e.id_produto = :id_produto 
                  AND e.variacao_id = :variacao_id 
                  AND e.armazenagem_id = :armazenagem_origem_id
                  AND e.status = 'ativo'
                LIMIT 1
            ", "id_produto={$input['id_produto']}&variacao_id={$input['variacao_id']}&armazenagem_origem_id={$input['armazenagem_origem_id']}");

            if (!$read->getResult()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Estoque não encontrado na armazenagem de origem']);
            }

            $estoque = $read->getResult()[0];
            
            if ($estoque['quantidade'] < $input['quantidade']) {
                return $this->jsonResponse(['success' => false, 'message' => 'Estoque insuficiente na armazenagem de origem']);
            }

            // Verificar se a armazenagem de destino existe e obter códigos
            $read->FullRead("SELECT id, codigo FROM armazenagens WHERE id = :id LIMIT 1", "id={$input['armazenagem_destino_id']}");
            if (!$read->getResult()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Armazenagem de destino não encontrada']);
            }
            $armazenagemDestino = $read->getResult()[0];
            
            // Obter código da armazenagem de origem
            $read->FullRead("SELECT id, codigo FROM armazenagens WHERE id = :id LIMIT 1", "id={$input['armazenagem_origem_id']}");
            $armazenagemOrigem = $read->getResult()[0];

            // 1. REDUZIR estoque da armazenagem de origem (SAÍDA)
            $update = new Update();
            $novoEstoqueOrigem = $estoque['quantidade'] - $input['quantidade'];
            $update->ExeUpdate('estoque', 
                ['quantidade' => $novoEstoqueOrigem], 
                "WHERE id = :id", 
                "id={$estoque['id']}"
            );

            // 2. ADICIONAR estoque na armazenagem de destino (ENTRADA)
            $read->FullRead("
                SELECT id, quantidade FROM estoque 
                WHERE id_produto = :produto_id 
                  AND variacao_id = :variacao_id 
                  AND armazenagem_id = :armazenagem_id
                  AND status = 'ativo'
                LIMIT 1
            ", "produto_id={$input['id_produto']}&variacao_id={$input['variacao_id']}&armazenagem_id={$input['armazenagem_destino_id']}");

            if ($read->getResult()) {
                // Atualizar estoque existente no destino
                $estoqueDestino = $read->getResult()[0];
                $novoEstoqueDestino = $estoqueDestino['quantidade'] + $input['quantidade'];
                $update->ExeUpdate('estoque', 
                    ['quantidade' => $novoEstoqueDestino], 
                    "WHERE id = :id", 
                    "id={$estoqueDestino['id']}"
                );
            } else {
                // Criar novo estoque na armazenagem de destino
                $create = new Create();
                $dadosEstoque = [
                    'id_produto' => $input['id_produto'],
                    'variacao_id' => $input['variacao_id'],
                    'armazenagem_id' => $input['armazenagem_destino_id'],
                    'quantidade' => $input['quantidade'],
                    'status' => 'ativo'
                ];
                
                error_log("Tentando criar estoque com dados: " . json_encode($dadosEstoque));
                
                $create->ExeCreate('estoque', $dadosEstoque);
                
                // Log para debug
                $estoqueDestinoId = $create->getResult();
                error_log("Criado novo estoque no destino - ID: {$estoqueDestinoId}, Quantidade: {$input['quantidade']}");
                
                // Verificar se a criação foi bem-sucedida
                if (!$estoqueDestinoId) {
                    error_log("ERRO: Falha ao criar estoque no destino");
                    return $this->jsonResponse(['success' => false, 'message' => 'Erro ao criar estoque no destino']);
                }
            }

            // 3. REGISTRAR MOVIMENTAÇÃO DE SAÍDA (origem)
            $create = new Create();
            $create->ExeCreate('movimentacoes_historico', [
                'tipo' => 'saida',
                'id_produto' => $input['id_produto'],
                'variacao_id' => $input['variacao_id'],
                'quantidade' => $input['quantidade'],
                'armazenagem_origem_id' => $input['armazenagem_origem_id'],
                'armazenagem_destino_id' => $input['armazenagem_destino_id'],
                'motivo' => $input['motivo'] ?? 'reorganizacao',
                'observacoes' => ($input['observacoes'] ?? '') . ' | Transferência: ' . $armazenagemOrigem['codigo'] . ' → ' . $armazenagemDestino['codigo'],
                'usuario_id' => $_SESSION[BASE.'user_id'] ?? 1,
                'data_movimentacao' => date('Y-m-d H:i:s')
            ]);

            // 4. REGISTRAR MOVIMENTAÇÃO DE ENTRADA (destino)
            $create->ExeCreate('movimentacoes_historico', [
                'tipo' => 'entrada',
                'id_produto' => $input['id_produto'],
                'variacao_id' => $input['variacao_id'],
                'quantidade' => $input['quantidade'],
                'armazenagem_origem_id' => $input['armazenagem_destino_id'],
                'armazenagem_destino_id' => $input['armazenagem_origem_id'],
                'motivo' => $input['motivo'] ?? 'reorganizacao',
                'observacoes' => ($input['observacoes'] ?? '') . ' | Transferência: ' . $armazenagemOrigem['codigo'] . ' → ' . $armazenagemDestino['codigo'],
                'usuario_id' => $_SESSION[BASE.'user_id'] ?? 1,
                'data_movimentacao' => date('Y-m-d H:i:s')
            ]);

            $transferenciaId = $create->getResult();

            // 5. ATUALIZAR CAPACIDADE DAS ARMAZENAGENS
            error_log("Iniciando atualização de capacidade - Origem: {$input['armazenagem_origem_id']}, Destino: {$input['armazenagem_destino_id']}");
            $this->atualizarCapacidadeArmazenagem($input['armazenagem_origem_id']);
            $this->atualizarCapacidadeArmazenagem($input['armazenagem_destino_id']);
            error_log("Atualização de capacidade concluída");

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Transferência realizada com sucesso',
                'transferencia_id' => $transferenciaId
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function atualizarCapacidadeArmazenagem($armazenagemId)
    {
        $read = new Read();
        $update = new Update();
        
        // Calcular capacidade atual baseada no estoque ativo
        $read->FullRead("
            SELECT COALESCE(SUM(quantidade), 0) as capacidade_atual
            FROM estoque
            WHERE armazenagem_id = :armazenagem_id AND status = 'ativo'
        ", "armazenagem_id={$armazenagemId}");
        
        $result = $read->getResult();
        $capacidadeAtual = $result[0]['capacidade_atual'] ?? 0;
        
        // Log para debug
        error_log("Atualizando capacidade armazenagem {$armazenagemId}: {$capacidadeAtual}");
        
        // Atualizar a capacidade na tabela armazenagens
        $update->ExeUpdate('armazenagens', 
            ['capacidade_atual' => $capacidadeAtual], 
            "WHERE id = :id", 
            "id={$armazenagemId}"
        );
        
        // Verificar se a atualização foi bem-sucedida
        $read->FullRead("SELECT capacidade_atual FROM armazenagens WHERE id = :id", "id={$armazenagemId}");
        $verificacao = $read->getResult();
        error_log("Capacidade após atualização: " . ($verificacao[0]['capacidade_atual'] ?? 'ERRO'));
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
