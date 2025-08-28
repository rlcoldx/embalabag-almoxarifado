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

        $requiredFields = ['sku', 'variacao_id', 'quantidade', 'armazenagem_origem_id', 'armazenagem_destino_id'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                return $this->jsonResponse(['success' => false, 'message' => "Campo {$field} é obrigatório"]);
            }
        }

        try {
            // Verificar se a variação existe e tem estoque suficiente na origem
            $read = new Read();
            $read->FullRead("
                SELECT pv.*, p.SKU, p.nome
                FROM produtos_variations pv
                INNER JOIN produtos p ON pv.id_produto = p.id
                WHERE pv.id = :variacao_id AND pv.armazenagem_id = :armazenagem_origem_id
                LIMIT 1
            ", "variacao_id={$input['variacao_id']}&armazenagem_origem_id={$input['armazenagem_origem_id']}");

            if (!$read->getResult()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Variação não encontrada na armazenagem de origem']);
            }

            $variacao = $read->getResult()[0];
            
            if ($variacao['estoque'] < $input['quantidade']) {
                return $this->jsonResponse(['success' => false, 'message' => 'Estoque insuficiente na armazenagem de origem']);
            }

            // Verificar se a armazenagem de destino existe
            $read->FullRead("SELECT id FROM armazenagens WHERE id = :id LIMIT 1", "id={$input['armazenagem_destino_id']}");
            if (!$read->getResult()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Armazenagem de destino não encontrada']);
            }

            // Criar registro de transferência
            $create = new Create();
            $transferenciaData = [
                'variacao_id' => $input['variacao_id'],
                'quantidade' => $input['quantidade'],
                'armazenagem_origem_id' => $input['armazenagem_origem_id'],
                'armazenagem_destino_id' => $input['armazenagem_destino_id'],
                'motivo' => $input['motivo'] ?? 'Transferência entre armazenagens',
                'prioridade' => $input['prioridade'] ?? 'normal',
                'observacoes' => $input['observacoes'] ?? '',
                'usuario_id' => $_SESSION['user_id'] ?? 1,
                'status' => 'pendente',
                'data_transferencia' => date('Y-m-d H:i:s')
            ];

            $create->ExeCreate('armazenagem_transferencias', $transferenciaData);

            if (!$create->getResult()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Erro ao criar transferência']);
            }

            $transferenciaId = $create->getResult();

            // Reduzir estoque da armazenagem de origem
            $update = new Update();
            $novoEstoqueOrigem = $variacao['estoque'] - $input['quantidade'];
            $update->ExeUpdate('produtos_variations', 
                ['estoque' => $novoEstoqueOrigem], 
                "WHERE id = :id", 
                "id={$input['variacao_id']}"
            );

            // Verificar se já existe a variação na armazenagem de destino
            $read->FullRead("
                SELECT id, estoque FROM produtos_variations 
                WHERE id_produto = :produto_id AND armazenagem_id = :armazenagem_id
                LIMIT 1
            ", "produto_id={$variacao['id_produto']}&armazenagem_id={$input['armazenagem_destino_id']}");

            if ($read->getResult()) {
                // Atualizar estoque existente
                $variacaoDestino = $read->getResult()[0];
                $novoEstoqueDestino = $variacaoDestino['estoque'] + $input['quantidade'];
                $update->ExeUpdate('produtos_variations', 
                    ['estoque' => $novoEstoqueDestino], 
                    "WHERE id = :id", 
                    "id={$variacaoDestino['id']}"
                );
            } else {
                // Criar nova variação na armazenagem de destino
                $create->ExeCreate('produtos_variations', [
                    'id_produto' => $variacao['id_produto'],
                    'cor' => $variacao['cor'],
                    'gerenciar_estoque' => $variacao['gerenciar_estoque'],
                    'estoque' => $input['quantidade'],
                    'encomenda' => $variacao['encomenda'],
                    'atraso' => $variacao['atraso'],
                    'armazenagem_id' => $input['armazenagem_destino_id'],
                    'estoque_minimo' => $variacao['estoque_minimo'] ?? 0,
                    'date_create' => date('Y-m-d H:i:s')
                ]);
            }

            // Marcar transferência como concluída
            $update->ExeUpdate('armazenagem_transferencias', 
                ['status' => 'concluida', 'data_conclusao' => date('Y-m-d H:i:s')], 
                "WHERE id = :id", 
                "id={$transferenciaId}"
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Transferência realizada com sucesso',
                'transferencia_id' => $transferenciaId
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
