<?php

namespace Agencia\Close\Models\NotaFiscal;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class NotaFiscal extends Model
{
    public function getAllNotasFiscais(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nf.*, 
                   p.numero as numero_pedido,
                   u1.nome as usuario_recebimento_nome,
                   u2.nome as usuario_conferencia_nome
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            LEFT JOIN usuarios u1 ON nf.usuario_recebimento = u1.id
            LEFT JOIN usuarios u2 ON nf.usuario_conferencia = u2.id
            ORDER BY nf.data_emissao DESC
        ");
        return $this->read;
    }

    public function getNotaFiscalById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nf.*, 
                   p.numero as numero_pedido,
                   u1.nome as usuario_recebimento_nome,
                   u2.nome as usuario_conferencia_nome
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            LEFT JOIN usuarios u1 ON nf.usuario_recebimento = u1.id
            LEFT JOIN usuarios u2 ON nf.usuario_conferencia = u2.id
            WHERE nf.id = :id
        ", "id={$id}");
        return $this->read;
    }

    public function getNotaFiscalByNumero(string $numero, ?string $serie = null): Read
    {
        $this->read = new Read();
        $where = "nf.numero = :numero";
        $params = "numero={$numero}";
        
        if ($serie) {
            $where .= " AND nf.serie = :serie";
            $params .= "&serie={$serie}";
        }
        
        $this->read->FullRead("
            SELECT nf.*, 
                   p.numero as numero_pedido
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            WHERE {$where}
        ", $params);
        return $this->read;
    }

    public function getNotasFiscaisByStatus(string $status): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nf.*, 
                   p.numero as numero_pedido
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            WHERE nf.status = :status
            ORDER BY nf.data_emissao DESC
        ", "status={$status}");
        return $this->read;
    }

    public function getNotasFiscaisByPedido(int $pedidoId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nf.*, 
                   p.numero as numero_pedido
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            WHERE nf.pedido_id = :pedido_id
            ORDER BY nf.data_emissao DESC
        ", "pedido_id={$pedidoId}");
        return $this->read;
    }

    public function createNotaFiscal(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("notas_fiscais", $data);
        return $this->create->getResult();
    }

    public function updateNotaFiscal(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("notas_fiscais", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteNotaFiscal(int $id): bool
    {
        // Verificar se há itens vinculados
        $this->read = new Read();
        $this->read->FullRead("SELECT COUNT(*) as total FROM pedidos_nf WHERE nota_fiscal_id = :id", "id={$id}");
        $result = $this->read->getResult();
        
        if ($result && $result[0]['total'] > 0) {
            return false; // Não pode deletar se há itens vinculados
        }
        
        $this->delete = new Delete();
        $this->delete->ExeDelete("notas_fiscais", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function marcarComoRecebida(int $id, int $usuarioId): bool
    {
        $data = [
            'status' => 'recebida',
            'data_recebimento' => date('Y-m-d'),
            'usuario_recebimento' => $usuarioId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->updateNotaFiscal($id, $data);
    }

    public function marcarComoConferida(int $id, int $usuarioId): bool
    {
        $data = [
            'status' => 'conferida',
            'usuario_conferencia' => $usuarioId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->updateNotaFiscal($id, $data);
    }

    public function vincularPedido(int $notaFiscalId, int $pedidoId): bool
    {
        $data = [
            'pedido_id' => $pedidoId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->updateNotaFiscal($notaFiscalId, $data);
    }

    public function buscarNotasFiscais(array $filtros = []): Read
    {
        $this->read = new Read();
        $sql = "
            SELECT nf.*, 
                   p.numero as numero_pedido,
                   u1.nome as usuario_recebimento_nome,
                   u2.nome as usuario_conferencia_nome
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            LEFT JOIN usuarios u1 ON nf.usuario_recebimento = u1.id
            LEFT JOIN usuarios u2 ON nf.usuario_conferencia = u2.id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['numero'])) {
            $sql .= " AND nf.numero LIKE :numero";
            $params .= "numero=%{$filtros['numero']}%&";
        }
        
        if (!empty($filtros['fornecedor'])) {
            $sql .= " AND nf.fornecedor LIKE :fornecedor";
            $params .= "fornecedor=%{$filtros['fornecedor']}%&";
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND nf.status = :status";
            $params .= "status={$filtros['status']}&";
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND nf.data_emissao >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND nf.data_emissao <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }
        
        $sql .= " ORDER BY nf.data_emissao DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        return $this->read;
    }

    public function getItemNFById(int $itemId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT pnf.*, 
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   nf.data_emissao
            FROM pedidos_nf pnf
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            WHERE pnf.id = :item_id
        ", "item_id={$itemId}");
        return $this->read;
    }

    public function getItensNFByNotaFiscal(int $notaFiscalId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT pnf.*
            FROM pedidos_nf pnf
            WHERE pnf.nota_fiscal_id = :nota_fiscal_id
            ORDER BY pnf.id
        ", "nota_fiscal_id={$notaFiscalId}");
        return $this->read;
    }

    public function getCountByStatus(string $status): int
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total 
            FROM notas_fiscais 
            WHERE status = :status
        ", "status={$status}");
        $result = $this->read->getResult();
        return $result ? (int)$result[0]['total'] : 0;
    }

    public function getCountRecebidasHoje(): int
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total 
            FROM notas_fiscais 
            WHERE DATE(data_recebimento) = CURDATE()
        ");
        $result = $this->read->getResult();
        return $result ? (int)$result[0]['total'] : 0;
    }

    public function getValorTotalMes(): float
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COALESCE(SUM(valor_total), 0) as total 
            FROM notas_fiscais 
            WHERE MONTH(data_emissao) = MONTH(CURDATE()) 
            AND YEAR(data_emissao) = YEAR(CURDATE())
        ");
        $result = $this->read->getResult();
        return $result ? (float)$result[0]['total'] : 0.0;
    }

    public function getEstatisticasPorStatus(): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT status, COUNT(*) as total
            FROM notas_fiscais 
            GROUP BY status
            ORDER BY total DESC
        ");
        $result = $this->read->getResult();
        
        $estatisticas = [];
        if ($result) {
            foreach ($result as $row) {
                $estatisticas[$row['status']] = (int)$row['total'];
            }
        }
        
        return $estatisticas;
    }

    public function getRecentes(int $limit = 5): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nf.*, 
                   p.numero as numero_pedido,
                   u1.nome as usuario_recebimento_nome
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            LEFT JOIN usuarios u1 ON nf.usuario_recebimento = u1.id
            ORDER BY nf.data_emissao DESC
            LIMIT :limit
        ", "limit={$limit}");
        
        $result = $this->read->getResult();
        return $result ?: [];
    }

    public function getAllItens(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT pnf.*, 
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   nf.data_emissao
            FROM pedidos_nf pnf
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            WHERE pnf.status = 'recebido'
            ORDER BY pnf.id DESC
        ");
        return $this->read;
    }

    public function gerarRelatorioRecebimento(array $filtros = []): array
    {
        $this->read = new Read();
        $sql = "
            SELECT 
                nf.*,
                p.numero as numero_pedido,
                u1.nome as usuario_recebimento_nome,
                u2.nome as usuario_conferencia_nome,
                COUNT(pnf.id) as total_itens,
                SUM(pnf.quantidade) as quantidade_total,
                SUM(pnf.valor_unitario * pnf.quantidade) as valor_total_itens
            FROM notas_fiscais nf
            LEFT JOIN pedidos p ON nf.pedido_id = p.id
            LEFT JOIN usuarios u1 ON nf.usuario_recebimento = u1.id
            LEFT JOIN usuarios u2 ON nf.usuario_conferencia = u2.id
            LEFT JOIN pedidos_nf pnf ON nf.id = pnf.nota_fiscal_id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND nf.data_emissao >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND nf.data_emissao <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND nf.status = :status";
            $params .= "status={$filtros['status']}&";
        }
        
        if (!empty($filtros['fornecedor'])) {
            $sql .= " AND nf.fornecedor LIKE :fornecedor";
            $params .= "fornecedor=%{$filtros['fornecedor']}%&";
        }
        
        $sql .= " GROUP BY nf.id ORDER BY nf.data_emissao DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        $result = $this->read->getResult();
        
        return [
            'dados' => $result ?: [],
            'total_registros' => count($result ?: []),
            'valor_total' => array_sum(array_column($result ?: [], 'valor_total')),
            'periodo' => [
                'inicio' => $filtros['data_inicio'] ?? null,
                'fim' => $filtros['data_fim'] ?? null
            ]
        ];
    }
}