<?php

namespace Agencia\Close\Models\Movimentacao;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class Movimentacao extends Model
{
    public function getAllMovimentacoes(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT m.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao,
                   u.nome as usuario_nome
            FROM movimentacoes m
            LEFT JOIN produtos p ON m.produto_id = p.id
            LEFT JOIN armazenagens a ON m.armazenagem_id = a.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            ORDER BY m.data_movimentacao DESC
        ");
        return $this->read;
    }

    public function getMovimentacaoById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT m.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao,
                   u.nome as usuario_nome
            FROM movimentacoes m
            LEFT JOIN produtos p ON m.produto_id = p.id
            LEFT JOIN armazenagens a ON m.armazenagem_id = a.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.id = :id
        ", "id={$id}");
        return $this->read;
    }

    public function getMovimentacoesByTipo(string $tipo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT m.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao,
                   u.nome as usuario_nome
            FROM movimentacoes m
            LEFT JOIN produtos p ON m.produto_id = p.id
            LEFT JOIN armazenagens a ON m.armazenagem_id = a.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.tipo = :tipo
            ORDER BY m.data_movimentacao DESC
        ", "tipo={$tipo}");
        return $this->read;
    }

    public function getMovimentacoesByProduto(int $produtoId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT m.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao,
                   u.nome as usuario_nome
            FROM movimentacoes m
            LEFT JOIN produtos p ON m.produto_id = p.id
            LEFT JOIN armazenagens a ON m.armazenagem_id = a.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.produto_id = :produto_id
            ORDER BY m.data_movimentacao DESC
        ", "produto_id={$produtoId}");
        return $this->read;
    }

    public function getMovimentacoesByArmazenagem(int $armazenagemId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT m.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao,
                   u.nome as usuario_nome
            FROM movimentacoes m
            LEFT JOIN produtos p ON m.produto_id = p.id
            LEFT JOIN armazenagens a ON m.armazenagem_id = a.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.armazenagem_id = :armazenagem_id
            ORDER BY m.data_movimentacao DESC
        ", "armazenagem_id={$armazenagemId}");
        return $this->read;
    }

    public function getSaldoProdutoArmazenagem(int $produtoId, int $armazenagemId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END), 0) as total_entradas,
                COALESCE(SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END), 0) as total_saidas,
                (COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END), 0)) as saldo_atual
            FROM movimentacoes 
            WHERE produto_id = :produto_id AND armazenagem_id = :armazenagem_id
        ", "produto_id={$produtoId}&armazenagem_id={$armazenagemId}");
        return $this->read;
    }

    public function createMovimentacao(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("movimentacoes", $data);
        return $this->create->getResult();
    }

    public function updateMovimentacao(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("movimentacoes", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteMovimentacao(int $id): bool
    {
        $this->delete = new Delete();
        $this->delete->ExeDelete("movimentacoes", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function registrarEntrada(int $produtoId, int $armazenagemId, int $quantidade, int $usuarioId, string $observacao = ''): bool
    {
        $data = [
            'produto_id' => $produtoId,
            'armazenagem_id' => $armazenagemId,
            'tipo' => 'entrada',
            'quantidade' => $quantidade,
            'usuario_id' => $usuarioId,
            'observacao' => $observacao,
            'data_movimentacao' => date('Y-m-d H:i:s')
        ];
        
        $movimentacaoId = $this->createMovimentacao($data);
        return $movimentacaoId !== false;
    }

    public function registrarSaida(int $produtoId, int $armazenagemId, int $quantidade, int $usuarioId, string $observacao = ''): bool
    {
        // Verificar se há estoque suficiente
        $saldo = $this->getSaldoProdutoArmazenagem($produtoId, $armazenagemId);
        $resultado = $saldo->getResult();
        
        if ($resultado && $resultado[0]['saldo_atual'] < $quantidade) {
            return false; // Estoque insuficiente
        }
        
        $data = [
            'produto_id' => $produtoId,
            'armazenagem_id' => $armazenagemId,
            'tipo' => 'saida',
            'quantidade' => $quantidade,
            'usuario_id' => $usuarioId,
            'observacao' => $observacao,
            'data_movimentacao' => date('Y-m-d H:i:s')
        ];
        
        $movimentacaoId = $this->createMovimentacao($data);
        return $movimentacaoId !== false;
    }

    public function registrarTransferencia(int $produtoId, int $armazenagemOrigemId, int $armazenagemDestinoId, int $quantidade, int $usuarioId, string $observacao = ''): bool
    {
        // Verificar se há estoque suficiente na origem
        $saldo = $this->getSaldoProdutoArmazenagem($produtoId, $armazenagemOrigemId);
        $resultado = $saldo->getResult();
        
        if ($resultado && $resultado[0]['saldo_atual'] < $quantidade) {
            return false; // Estoque insuficiente na origem
        }
        
        // Registrar saída da origem
        $saida = $this->registrarSaida($produtoId, $armazenagemOrigemId, $quantidade, $usuarioId, "Transferência para " . $armazenagemDestinoId);
        
        if (!$saida) {
            return false;
        }
        
        // Registrar entrada no destino
        $entrada = $this->registrarEntrada($produtoId, $armazenagemDestinoId, $quantidade, $usuarioId, "Transferência de " . $armazenagemOrigemId);
        
        return $entrada;
    }

    public function buscarMovimentacoes(array $filtros = []): Read
    {
        $this->read = new Read();
        $sql = "
            SELECT m.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao,
                   u.nome as usuario_nome
            FROM movimentacoes m
            LEFT JOIN produtos p ON m.produto_id = p.id
            LEFT JOIN armazenagens a ON m.armazenagem_id = a.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = :tipo";
            $params .= "tipo={$filtros['tipo']}&";
        }
        
        if (!empty($filtros['produto_id'])) {
            $sql .= " AND m.produto_id = :produto_id";
            $params .= "produto_id={$filtros['produto_id']}&";
        }
        
        if (!empty($filtros['armazenagem_id'])) {
            $sql .= " AND m.armazenagem_id = :armazenagem_id";
            $params .= "armazenagem_id={$filtros['armazenagem_id']}&";
        }
        
        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $sql .= " AND m.data_movimentacao BETWEEN :data_inicio AND :data_fim";
            $params .= "data_inicio={$filtros['data_inicio']}&data_fim={$filtros['data_fim']}&";
        }
        
        $sql .= " ORDER BY m.data_movimentacao DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        return $this->read;
    }
}