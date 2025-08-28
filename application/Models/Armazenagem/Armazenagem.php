<?php

namespace Agencia\Close\Models\Armazenagem;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class Armazenagem extends Model
{
    public function getAllArmazenagens(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT a.*, 
                   a.capacidade_atual as quantidade_ocupada
            FROM armazenagens a 
            ORDER BY a.codigo ASC
        ");
        return $this->read;
    }

    public function getArmazenagensAtivas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT a.*, 
                   a.capacidade_atual as quantidade_ocupada
            FROM armazenagens a 
            WHERE a.status = 'ativo' 
            ORDER BY a.codigo ASC
        ");
        return $this->read;
    }

    public function getArmazenagemById($id = null): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT a.*, 
                   a.capacidade_atual as quantidade_ocupada
            FROM armazenagens a
            WHERE a.id = :id
        ", "id={$id}");
        return $this->read;
    }

    public function getArmazenagemByCodigo(string $codigo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM armazenagens WHERE codigo = :codigo AND status = 'ativo'", "codigo={$codigo}");
        return $this->read;
    }

    public function getArmazenagensByTipo(string $tipo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM armazenagens WHERE tipo = :tipo AND status = 'ativo' ORDER BY codigo ASC", "tipo={$tipo}");
        return $this->read;
    }

    public function getArmazenagensBySetor(string $setor): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM armazenagens WHERE setor = :setor AND status = 'ativo' ORDER BY codigo ASC", "setor={$setor}");
        return $this->read;
    }

    public function getMapaArmazenagens(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT a.*, 
                   a.capacidade_atual as quantidade_ocupada,
                   (a.capacidade_maxima - a.capacidade_atual) as capacidade_disponivel
            FROM armazenagens a
            WHERE a.status = 'ativo'
            ORDER BY a.setor, a.codigo
        ");
        return $this->read;
    }

    public function createArmazenagem(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("armazenagens", $data);
        return $this->create->getResult();
    }

    public function updateArmazenagem(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("armazenagens", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteArmazenagem(int $id): bool
    {
        // Verificar se há itens alocados nesta armazenagem
        $this->read = new Read();
        $this->read->FullRead("SELECT COUNT(*) as total FROM itens_nf WHERE armazenagem_id = :id AND status = 'alocado'", "id={$id}");
        $result = $this->read->getResult();
        
        if ($result && $result[0]['total'] > 0) {
            return false; // Não pode deletar se há itens alocados
        }
        
        $this->delete = new Delete();
        $this->delete->ExeDelete("armazenagens", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }



    public function alterarStatus(int $id, string $status): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("armazenagens", ['status' => $status], "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function buscarArmazenagens(array $filtros = []): Read
    {
        $this->read = new Read();
        $sql = "SELECT a.*, a.capacidade_atual as quantidade_ocupada FROM armazenagens a WHERE a.status = 'ativo'";
        $params = "";
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND a.tipo = :tipo";
            $params .= "tipo={$filtros['tipo']}&";
        }
        
        if (!empty($filtros['setor'])) {
            $sql .= " AND a.setor = :setor";
            $params .= "setor={$filtros['setor']}&";
        }
        
        if (!empty($filtros['codigo'])) {
            $sql .= " AND a.codigo LIKE :codigo";
            $params .= "codigo=%{$filtros['codigo']}%&";
        }
        
        $sql .= " ORDER BY a.setor, a.codigo ASC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        return $this->read;
    }

    public function getProdutosArmazenados(int $armazenagemId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                e.id as estoque_id,
                p.nome as nome_produto,
                p.SKU as sku,
                pv.cor as nome_cor,
                e.quantidade,
                a.codigo as codigo_armazenagem
            FROM estoque e
            INNER JOIN produtos p ON e.id_produto = p.id
            INNER JOIN produtos_variations pv ON pv.id_produto = p.id AND pv.id = e.variacao_id
            INNER JOIN armazenagens a ON e.armazenagem_id = a.id
            WHERE e.armazenagem_id = :armazenagem_id AND e.quantidade > 0
            ORDER BY p.nome, pv.cor
        ", "armazenagem_id={$armazenagemId}");
        return $this->read;
    }

    public function getEstatisticasArmazenagem(int $armazenagemId): array
    {
        $this->read = new Read();
        
        // Total de quantidade
        $this->read->FullRead("
            SELECT COALESCE(SUM(e.quantidade), 0) as total_quantidade
            FROM estoque e
            WHERE e.armazenagem_id = :armazenagem_id
        ", "armazenagem_id={$armazenagemId}");
        $totalQuantidade = $this->read->getResult();
        
        // Movimentações de entrada
        $this->read->FullRead("
            SELECT COUNT(*) as total_entradas
            FROM movimentacoes_historico
            WHERE armazenagem_origem_id = :armazenagem_id AND tipo = 'entrada'
        ", "armazenagem_id={$armazenagemId}");
        $entradas = $this->read->getResult();
        
        // Movimentações de saída
        $this->read->FullRead("
            SELECT COUNT(*) as total_saidas
            FROM movimentacoes_historico
            WHERE armazenagem_origem_id = :armazenagem_id AND tipo = 'saida'
        ", "armazenagem_id={$armazenagemId}");
        $saidas = $this->read->getResult();
        
        return [
            'total_quantidade' => $totalQuantidade[0]['total_quantidade'] ?? 0,
            'movimentacoes_entrada' => $entradas[0]['total_entradas'] ?? 0,
            'movimentacoes_saida' => $saidas[0]['total_saidas'] ?? 0
        ];
    }
}