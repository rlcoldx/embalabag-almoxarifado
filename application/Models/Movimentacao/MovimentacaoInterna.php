<?php

namespace Agencia\Close\Models\Movimentacao;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class MovimentacaoInterna extends Model
{
    public function getAllMovimentacoes(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT mi.*, 
                   pnf.codigo_produto,
                   pnf.descricao_produto,
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            ORDER BY mi.data_movimentacao DESC
        ");
        return $this->read;
    }

    public function getMovimentacaoById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT mi.*, 
                   pnf.codigo_produto,
                   pnf.descricao_produto,
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            WHERE mi.id = :id
        ", "id={$id}");
        return $this->read;
    }

    public function getMovimentacoesByItemNF(int $itemNfId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT mi.*, 
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            WHERE mi.item_nf_id = :item_nf_id
            ORDER BY mi.data_movimentacao DESC
        ", "item_nf_id={$itemNfId}");
        return $this->read;
    }

    public function getMovimentacoesByArmazenagem(int $armazenagemId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT mi.*, 
                   pnf.codigo_produto,
                   pnf.descricao_produto,
                   nf.numero as numero_nf,
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            WHERE mi.armazenagem_origem_id = :armazenagem_id OR mi.armazenagem_destino_id = :armazenagem_id
            ORDER BY mi.data_movimentacao DESC
        ", "armazenagem_id={$armazenagemId}");
        return $this->read;
    }

    public function getMovimentacoesByTipo(string $tipo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT mi.*, 
                   pnf.codigo_produto,
                   pnf.descricao_produto,
                   nf.numero as numero_nf,
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            WHERE mi.tipo_movimentacao = :tipo
            ORDER BY mi.data_movimentacao DESC
        ", "tipo={$tipo}");
        return $this->read;
    }

    public function createMovimentacao(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("movimentacoes_internas", $data);
        return $this->create->getResult();
    }

    public function updateMovimentacao(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("movimentacoes_internas", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteMovimentacao(int $id): bool
    {
        // Verificar se a movimentação já foi executada
        $this->read = new Read();
        $this->read->FullRead("
            SELECT status FROM movimentacoes_internas WHERE id = :id
        ", "id={$id}");
        $result = $this->read->getResult();
        
        if ($result && $result[0]['status'] === 'concluida') {
            return false; // Não pode deletar movimentação concluída
        }
        
        $this->delete = new Delete();
        $this->delete->ExeDelete("movimentacoes_internas", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function executarMovimentacao(int $id): bool
    {
        // Verificar se a movimentação está pendente
        $this->read = new Read();
        $this->read->FullRead("
            SELECT * FROM movimentacoes_internas WHERE id = :id
        ", "id={$id}");
        $movimentacao = $this->read->getResult();
        
        if (!$movimentacao || $movimentacao[0]['status'] !== 'pendente') {
            return false;
        }
        
        $mov = $movimentacao[0];
        
        // Atualizar status da movimentação
        $data = [
            'status' => 'concluida',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!$this->updateMovimentacao($id, $data)) {
            return false;
        }
        
        // Atualizar armazenagem de destino no item da NF
        $this->update = new Update();
        $this->update->ExeUpdate("pedidos_nf", 
            ['armazenagem_id' => $mov['armazenagem_destino_id'], 'status' => 'alocado'], 
            "WHERE id = :id", 
            "id={$mov['item_nf_id']}"
        );
        
        return $this->update->getResult() === true;
    }

    public function realizarPutAway(array $data): int|false
    {
        // Validar dados obrigatórios
        if (empty($data['item_nf_id']) || empty($data['armazenagem_destino_id']) || 
            empty($data['usuario_movimentacao']) || empty($data['quantidade_movimentada'])) {
            return false;
        }

        // Verificar se o item já está alocado
        $this->read = new Read();
        $this->read->FullRead("
            SELECT armazenagem_id, status FROM pedidos_nf WHERE id = :id
        ", "id={$data['item_nf_id']}");
        $item = $this->read->getResult();
        
        if (!$item) {
            return false;
        }
        
        // Se já está alocado, definir como origem
        if ($item[0]['armazenagem_id']) {
            $data['armazenagem_origem_id'] = $item[0]['armazenagem_id'];
        }
        
        // Definir tipo como put-away se não especificado
        if (empty($data['tipo_movimentacao'])) {
            $data['tipo_movimentacao'] = 'put_away';
        }
        
        // Definir status como pendente
        $data['status'] = 'pendente';
        
        return $this->createMovimentacao($data);
    }

    public function buscarMovimentacoes(array $filtros = []): Read
    {
        $this->read = new Read();
        $sql = "
            SELECT mi.*, 
                   pnf.codigo_produto,
                   pnf.descricao_produto,
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['numero_nf'])) {
            $sql .= " AND nf.numero LIKE :numero_nf";
            $params .= "numero_nf=%{$filtros['numero_nf']}%&";
        }
        
        if (!empty($filtros['codigo_produto'])) {
            $sql .= " AND pnf.codigo_produto LIKE :codigo_produto";
            $params .= "codigo_produto=%{$filtros['codigo_produto']}%&";
        }
        
        if (!empty($filtros['tipo_movimentacao'])) {
            $sql .= " AND mi.tipo_movimentacao = :tipo_movimentacao";
            $params .= "tipo_movimentacao={$filtros['tipo_movimentacao']}&";
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND mi.status = :status";
            $params .= "status={$filtros['status']}&";
        }
        
        if (!empty($filtros['usuario_movimentacao'])) {
            $sql .= " AND mi.usuario_movimentacao = :usuario_movimentacao";
            $params .= "usuario_movimentacao={$filtros['usuario_movimentacao']}&";
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND mi.data_movimentacao >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND mi.data_movimentacao <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }
        
        $sql .= " ORDER BY mi.data_movimentacao DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        return $this->read;
    }

    public function getEstatisticasMovimentacao(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                COUNT(*) as total_movimentacoes,
                SUM(CASE WHEN mi.status = 'pendente' THEN 1 ELSE 0 END) as movimentacoes_pendentes,
                SUM(CASE WHEN mi.status = 'em_andamento' THEN 1 ELSE 0 END) as movimentacoes_em_andamento,
                SUM(CASE WHEN mi.status = 'concluida' THEN 1 ELSE 0 END) as movimentacoes_concluidas,
                SUM(CASE WHEN mi.status = 'cancelada' THEN 1 ELSE 0 END) as movimentacoes_canceladas,
                SUM(CASE WHEN mi.tipo_movimentacao = 'put_away' THEN 1 ELSE 0 END) as put_aways,
                SUM(CASE WHEN mi.tipo_movimentacao = 'transferencia' THEN 1 ELSE 0 END) as transferencias,
                SUM(mi.quantidade_movimentada) as total_quantidade_movimentada
            FROM movimentacoes_internas mi
        ");
        return $this->read;
    }

    public function getCountByStatus(string $status): int
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total 
            FROM movimentacoes_internas 
            WHERE status = :status
        ", "status={$status}");
        $result = $this->read->getResult();
        return $result ? (int)$result[0]['total'] : 0;
    }

    public function getEstatisticasPorTipo(): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                DATE(data_movimentacao) as data,
                SUM(CASE WHEN tipo_movimentacao = 'put_away' THEN 1 ELSE 0 END) as put_away,
                SUM(CASE WHEN tipo_movimentacao = 'transferencia' THEN 1 ELSE 0 END) as transferencia,
                SUM(CASE WHEN tipo_movimentacao = 'reposicao' THEN 1 ELSE 0 END) as reposicao
            FROM movimentacoes_internas 
            WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(data_movimentacao)
            ORDER BY data DESC
            LIMIT 30
        ");
        $result = $this->read->getResult();
        
        $estatisticas = [];
        if ($result) {
            foreach ($result as $row) {
                $estatisticas[$row['data']] = [
                    'put_away' => (int)$row['put_away'],
                    'transferencia' => (int)$row['transferencia'],
                    'reposicao' => (int)$row['reposicao']
                ];
            }
        }
        
        return $estatisticas;
    }

    public function getRecentes(int $limit = 5): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT mi.*, 
                   pnf.codigo_produto,
                   pnf.descricao_produto,
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   a1.codigo as armazenagem_origem_codigo,
                   a1.descricao as armazenagem_origem_descricao,
                   a2.codigo as armazenagem_destino_codigo,
                   a2.descricao as armazenagem_destino_descricao,
                   u.nome as usuario_movimentacao_nome
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            ORDER BY mi.data_movimentacao DESC
            LIMIT :limit
        ", "limit={$limit}");
        
        $result = $this->read->getResult();
        return $result ?: [];
    }

    public function getAll(): Read
    {
        return $this->getAllMovimentacoes();
    }

    public function getById(int $id): Read
    {
        return $this->getMovimentacaoById($id);
    }

    public function create(array $data): int|false
    {
        return $this->createMovimentacao($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateMovimentacao($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteMovimentacao($id);
    }

    public function executar(int $id): bool
    {
        return $this->executarMovimentacao($id);
    }

    public function gerarRelatorioMovimentacao(array $filtros = []): array
    {
        $this->read = new Read();
        $sql = "
            SELECT 
                mi.*,
                pnf.codigo_produto,
                pnf.descricao_produto,
                nf.numero as numero_nf,
                nf.fornecedor,
                a1.codigo as armazenagem_origem_codigo,
                a1.descricao as armazenagem_origem_descricao,
                a2.codigo as armazenagem_destino_codigo,
                a2.descricao as armazenagem_destino_descricao,
                u.nome as usuario_movimentacao_nome,
                CASE 
                    WHEN mi.tipo_movimentacao = 'put_away' THEN 'Put-Away'
                    WHEN mi.tipo_movimentacao = 'transferencia' THEN 'Transferência'
                    WHEN mi.tipo_movimentacao = 'reposicao' THEN 'Reposição'
                    ELSE mi.tipo_movimentacao
                END as tipo_movimentacao_descricao,
                CASE 
                    WHEN mi.status = 'pendente' THEN 'Pendente'
                    WHEN mi.status = 'em_andamento' THEN 'Em Andamento'
                    WHEN mi.status = 'concluida' THEN 'Concluída'
                    WHEN mi.status = 'cancelada' THEN 'Cancelada'
                    ELSE mi.status
                END as status_descricao
            FROM movimentacoes_internas mi
            INNER JOIN pedidos_nf pnf ON mi.item_nf_id = pnf.id
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            LEFT JOIN armazenagens a1 ON mi.armazenagem_origem_id = a1.id
            INNER JOIN armazenagens a2 ON mi.armazenagem_destino_id = a2.id
            INNER JOIN usuarios u ON mi.usuario_movimentacao = u.id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND mi.data_movimentacao >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND mi.data_movimentacao <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }
        
        if (!empty($filtros['tipo_movimentacao'])) {
            $sql .= " AND mi.tipo_movimentacao = :tipo_movimentacao";
            $params .= "tipo_movimentacao={$filtros['tipo_movimentacao']}&";
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND mi.status = :status";
            $params .= "status={$filtros['status']}&";
        }
        
        if (!empty($filtros['usuario_movimentacao'])) {
            $sql .= " AND mi.usuario_movimentacao = :usuario_movimentacao";
            $params .= "usuario_movimentacao={$filtros['usuario_movimentacao']}&";
        }
        
        if (!empty($filtros['armazenagem_destino'])) {
            $sql .= " AND a2.codigo LIKE :armazenagem_destino";
            $params .= "armazenagem_destino=%{$filtros['armazenagem_destino']}%&";
        }
        
        $sql .= " ORDER BY mi.data_movimentacao DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        $result = $this->read->getResult();
        
        return [
            'dados' => $result ?: [],
            'total_registros' => count($result ?: []),
            'total_movimentacoes' => count($result ?: []),
            'movimentacoes_concluidas' => count(array_filter($result ?: [], function($item) {
                return $item['status'] === 'concluida';
            })),
            'movimentacoes_pendentes' => count(array_filter($result ?: [], function($item) {
                return $item['status'] === 'pendente';
            })),
            'total_quantidade_movimentada' => array_sum(array_column($result ?: [], 'quantidade_movimentada')),
            'periodo' => [
                'inicio' => $filtros['data_inicio'] ?? null,
                'fim' => $filtros['data_fim'] ?? null
            ]
        ];
    }
} 