<?php

namespace Agencia\Close\Models\Pedidos;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class PedidosItens extends Model
{
    public function getItensPorPedido($pedido_id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                pi.*,
                p.nome as produto_nome,
                p.codigo as produto_codigo,
                p.descricao as produto_descricao,
                u.nome as usuario_nome
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON pi.id_produto = p.id
            LEFT JOIN usuarios u ON pi.id_user = u.id
            WHERE pi.id_pedido = :pedido_id
            ORDER BY pi.data DESC
        ", "pedido_id={$pedido_id}");
        return $read;
    }

    public function getItensPorUsuario($user_id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                pi.*,
                p.nome as produto_nome,
                p.codigo as produto_codigo,
                p.descricao as produto_descricao,
                ped.codigo as pedido_codigo,
                ped.status_pedido as pedido_status
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON pi.id_produto = p.id
            LEFT JOIN pedidos ped ON pi.id_pedido = ped.id
            WHERE pi.id_user = :user_id
            ORDER BY pi.data DESC
        ", "user_id={$user_id}");
        return $read;
    }

    public function getItensPorProduto($produto_id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                pi.*,
                p.nome as produto_nome,
                p.codigo as produto_codigo,
                ped.codigo as pedido_codigo,
                ped.status_pedido as pedido_status,
                u.nome as usuario_nome
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON pi.id_produto = p.id
            LEFT JOIN pedidos ped ON pi.id_pedido = ped.id
            LEFT JOIN usuarios u ON pi.id_user = u.id
            WHERE pi.id_produto = :produto_id
            ORDER BY pi.data DESC
        ", "produto_id={$produto_id}");
        return $read;
    }

    public function getItensEncomenda(): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                pi.*,
                p.nome as produto_nome,
                p.codigo as produto_codigo,
                p.descricao as produto_descricao,
                ped.codigo as pedido_codigo,
                ped.status_pedido as pedido_status,
                u.nome as usuario_nome
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON pi.id_produto = p.id
            LEFT JOIN pedidos ped ON pi.id_pedido = ped.id
            LEFT JOIN usuarios u ON pi.id_user = u.id
            WHERE pi.encomenda = 'yes'
            ORDER BY pi.data DESC
        ");
        return $read;
    }

    public function getItensEscolhidos($pedido_id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                pi.*,
                p.nome as produto_nome,
                p.codigo as produto_codigo,
                p.descricao as produto_descricao
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON pi.id_produto = p.id
            WHERE pi.id_pedido = :pedido_id AND pi.escolhido = 'yes'
            ORDER BY pi.data DESC
        ", "pedido_id={$pedido_id}");
        return $read;
    }

    public function createItem($params): bool
    {
        try {
            $create = new Create();
            $item = [
                'id_pedido' => $params['id_pedido'],
                'id_user' => $params['id_user'] ?? $_SESSION['user_id'] ?? 1,
                'id_produto' => $params['id_produto'],
                'qty' => $params['qty'] ?? 1,
                'qty_base' => $params['qty_base'] ?? $params['qty'] ?? 1,
                'variavel' => $params['variavel'] ?? null,
                'valor_unidade' => $params['valor_unidade'] ?? '0.00',
                'valor_total' => $params['valor_total'] ?? '0.00',
                'gerenciar_estoque' => $params['gerenciar_estoque'] ?? 'yes',
                'encomenda' => $params['encomenda'] ?? 'no',
                'atraso' => $params['atraso'] ?? 0,
                'escolhido' => $params['escolhido'] ?? 'no'
            ];
            
            $create->ExeCreate('pedidos_itens', $item);
            return $create->getResult();
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateItem($pedido_id, $produto_id, $params): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos_itens', 
                $params, 
                'WHERE id_pedido = :pedido_id AND id_produto = :produto_id', 
                "pedido_id={$pedido_id}&produto_id={$produto_id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function marcarComoEscolhido($pedido_id, $produto_id): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos_itens', 
                ['escolhido' => 'yes'], 
                'WHERE id_pedido = :pedido_id AND id_produto = :produto_id', 
                "pedido_id={$pedido_id}&produto_id={$produto_id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function marcarComoEncomenda($pedido_id, $produto_id): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos_itens', 
                ['encomenda' => 'yes'], 
                'WHERE id_pedido = :pedido_id AND id_produto = :produto_id', 
                "pedido_id={$pedido_id}&produto_id={$produto_id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteItem($pedido_id, $produto_id): bool
    {
        try {
            $delete = new Delete();
            $delete->ExeDelete('pedidos_itens', 
                'WHERE id_pedido = :pedido_id AND id_produto = :produto_id', 
                "pedido_id={$pedido_id}&produto_id={$produto_id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteItensPorPedido($pedido_id): bool
    {
        try {
            $delete = new Delete();
            $delete->ExeDelete('pedidos_itens', 
                'WHERE id_pedido = :pedido_id', 
                "pedido_id={$pedido_id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function calcularValorTotal($pedido_id): float
    {
        $read = new Read();
        $read->FullRead("
            SELECT SUM(CAST(valor_total AS DECIMAL(10,2))) as total
            FROM pedidos_itens 
            WHERE id_pedido = :pedido_id
        ", "pedido_id={$pedido_id}");
        
        $result = $read->getResult();
        return $result[0]['total'] ?? 0.00;
    }

    public function getQuantidadeTotal($pedido_id): int
    {
        $read = new Read();
        $read->FullRead("
            SELECT SUM(qty) as total
            FROM pedidos_itens 
            WHERE id_pedido = :pedido_id
        ", "pedido_id={$pedido_id}");
        
        $result = $read->getResult();
        return $result[0]['total'] ?? 0;
    }
} 