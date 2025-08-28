<?php

namespace Agencia\Close\Models\Transferencias;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Transferencias extends Model
{
    public function getTransferencias(): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                t.*,
                i.codigo as item_codigo,
                i.descricao as item_descricao,
                ao.codigo as origem_codigo,
                ao.nome as origem_nome,
                ad.codigo as destino_codigo,
                ad.nome as destino_nome,
                us.nome as solicitante_nome,
                ue.nome as executor_nome
            FROM armazenagem_transferencias t
            LEFT JOIN itens_nf i ON t.item_id = i.id
            LEFT JOIN armazenagens ao ON t.armazenagem_origem = ao.id
            LEFT JOIN armazenagens ad ON t.armazenagem_destino = ad.id
            LEFT JOIN usuarios us ON t.usuario_solicitante = us.id
            LEFT JOIN usuarios ue ON t.usuario_executor = ue.id
            ORDER BY t.data_solicitacao DESC
        ");
        return $read;
    }

    public function getTransferencia($id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                t.*,
                i.codigo as item_codigo,
                i.descricao as item_descricao,
                ao.codigo as origem_codigo,
                ao.nome as origem_nome,
                ad.codigo as destino_codigo,
                ad.nome as destino_nome,
                us.nome as solicitante_nome,
                ue.nome as executor_nome
            FROM armazenagem_transferencias t
            LEFT JOIN itens_nf i ON t.item_id = i.id
            LEFT JOIN armazenagens ao ON t.armazenagem_origem = ao.id
            LEFT JOIN armazenagens ad ON t.armazenagem_destino = ad.id
            LEFT JOIN usuarios us ON t.usuario_solicitante = us.id
            LEFT JOIN usuarios ue ON t.usuario_executor = ue.id
            WHERE t.id = :id
        ", "id={$id}");
        return $read;
    }

    public function createTransferencia($params): bool
    {
        try {
            $create = new Create();
            $transferencia = [
                'item_id' => $params['item_id'],
                'armazenagem_origem' => $params['armazenagem_origem'],
                'armazenagem_destino' => $params['armazenagem_destino'],
                'quantidade' => $params['quantidade'],
                'motivo' => $params['motivo'],
                'observacoes' => $params['observacoes'] ?? '',
                'usuario_solicitante' => $_SESSION['user_id'] ?? 1
            ];
            
            $create->ExeCreate('armazenagem_transferencias', $transferencia);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function executarTransferencia($id): bool
    {
        try {
            // Buscar transferência
            $read = new Read();
            $read->FullRead("SELECT * FROM armazenagem_transferencias WHERE id = :id", "id={$id}");
            $transferencia = $read->getResult()[0];

            if (!$transferencia || $transferencia['status'] !== 'pendente') {
                return false;
            }

            // Verificar se há quantidade suficiente na origem
            $read = new Read();
            $read->FullRead("
                SELECT quantidade FROM itens_nf 
                WHERE id = :item_id AND armazenagem_id = :armazenagem_id
            ", "item_id={$transferencia['item_id']}&armazenagem_id={$transferencia['armazenagem_origem']}");
            
            $item_origem = $read->getResult()[0];
            if (!$item_origem || $item_origem['quantidade'] < $transferencia['quantidade']) {
                return false;
            }

            // Verificar capacidade do destino
            $read = new Read();
            $read->FullRead("
                SELECT capacidade, quantidade_atual FROM armazenagens 
                WHERE id = :id
            ", "id={$transferencia['armazenagem_destino']}");
            
            $destino = $read->getResult()[0];
            if (!$destino || ($destino['quantidade_atual'] + $transferencia['quantidade']) > $destino['capacidade']) {
                return false;
            }

            // Executar transferência
            $update = new Update();
            
            // Reduzir quantidade na origem
            $update->ExeUpdate('itens_nf', 
                ['quantidade' => $item_origem['quantidade'] - $transferencia['quantidade']], 
                'WHERE id = :id', 
                "id={$transferencia['item_id']}"
            );

            // Adicionar quantidade no destino
            $update->ExeUpdate('itens_nf', 
                ['quantidade' => $destino['quantidade_atual'] + $transferencia['quantidade']], 
                'WHERE id = :id', 
                "id={$transferencia['item_id']}"
            );

            // Atualizar status da transferência
            $update->ExeUpdate('armazenagem_transferencias', 
                [
                    'status' => 'concluida',
                    'usuario_executor' => $_SESSION['user_id'] ?? 1,
                    'data_execucao' => date('Y-m-d H:i:s'),
                    'data_conclusao' => date('Y-m-d H:i:s')
                ], 
                'WHERE id = :id', 
                "id={$id}"
            );

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function cancelarTransferencia($id): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('armazenagem_transferencias', 
                ['status' => 'cancelada'], 
                'WHERE id = :id AND status = :status', 
                "id={$id}&status=pendente"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
} 