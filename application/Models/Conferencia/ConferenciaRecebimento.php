<?php

namespace Agencia\Close\Models\Conferencia;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class ConferenciaRecebimento extends Model
{
    /**
     * Obter todas as conferências
     */
    public function getAllConferencias(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                cr.*,
                COALESCE(nfe.numero_nfe, '-') as numero_nfe,
                COALESCE(f.nome, '-') as fornecedor_nome,
                COALESCE(p.nome, '-') as produto_nome,
                COALESCE(p.SKU, '-') as produto_sku,
                COALESCE(pv.tamanho, '-') as tamanho,
                COALESCE(c.nome, '-') as cor_nome,
                COALESCE(u.nome, '-') as usuario_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN produtos_variations pv ON cr.variacao_id = pv.id
            LEFT JOIN cores c ON pv.cor = c.id
            LEFT JOIN usuarios u ON cr.usuario_conferente_id = u.id
            ORDER BY cr.created_at DESC
        ");
        return $this->read;
    }

    /**
     * Obter conferência por ID
     */
    public function getConferenciaById($id = null): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                cr.*,
                nfe.numero_nfe,
                f.nome as fornecedor_nome,
                nfe.data_emissao,
                p.nome as produto_nome,
                p.SKU as produto_sku,
                p.categoria,
                pv.tamanho,
                pv.estoque,
                c.nome as cor_nome,
                u.nome as usuario_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN produtos_variations pv ON cr.variacao_id = pv.id
            LEFT JOIN cores c ON pv.cor = c.id
            LEFT JOIN usuarios u ON cr.usuario_conferente_id = u.id
            WHERE cr.id = :id
            LIMIT 1
        ", "id={$id}");
        return $this->read;
    }

    /**
     * Obter conferências por NFE
     */
    public function getConferenciasByNfe(int $nfeId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                cr.*,
                p.nome as produto_nome,
                p.SKU as produto_sku,
                pv.tamanho,
                c.nome as cor_nome,
                u.nome as usuario_nome
            FROM conferencia_recebimento cr
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN produtos_variations pv ON cr.variacao_id = pv.id
            LEFT JOIN cores c ON pv.cor = c.id
            LEFT JOIN usuarios u ON cr.usuario_conferente_id = u.id
            WHERE cr.nfe_id = :nfe_id
            ORDER BY cr.created_at ASC
        ", "nfe_id={$nfeId}");
        return $this->read;
    }

    /**
     * Obter conferências pendentes
     */
    public function getConferenciasPendentes(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                cr.*,
                nfe.numero_nfe,
                f.nome as fornecedor_nome,
                p.nome as produto_nome,
                p.SKU as produto_sku,
                pv.tamanho,
                c.nome as cor_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN produtos_variations pv ON cr.variacao_id = pv.id
            LEFT JOIN cores c ON pv.cor = c.id
            WHERE cr.status_conferencia IN ('pendente', 'em_andamento')
            ORDER BY cr.created_at ASC
        ");
        return $this->read;
    }

    /**
     * Buscar conferência existente para item da NF-e
     */
    public function getConferenciaExistente(int $nfeId, int $produtoId, int $variacaoId, ?int $itemNfeId = null): Read
    {
        $this->read = new Read();

        if ($itemNfeId) {
            $this->read->FullRead("
                SELECT * FROM conferencia_recebimento
                WHERE nfe_id = :nfe_id AND item_nfe_id = :item_nfe_id
                LIMIT 1
            ", "nfe_id={$nfeId}&item_nfe_id={$itemNfeId}");
            return $this->read;
        }

        $this->read->FullRead("
            SELECT * FROM conferencia_recebimento
            WHERE nfe_id = :nfe_id
              AND produto_id = :produto_id
              AND variacao_id = :variacao_id
            LIMIT 1
        ", "nfe_id={$nfeId}&produto_id={$produtoId}&variacao_id={$variacaoId}");
        return $this->read;
    }

    /**
     * Criar nova conferência
     */
    public function criarConferencia(array $dados): Create
    {
        $this->create = new Create();
        $this->create->ExeCreate('conferencia_recebimento', $dados);
        return $this->create;
    }

    /**
     * Atualizar conferência
     */
    public function atualizarConferencia(int $id, array $dados): Update
    {
        $this->update = new Update();
        $this->update->ExeUpdate('conferencia_recebimento', $dados, "WHERE id = :id", "id={$id}");
        return $this->update;
    }

    public function excluirConferencia(int $id): bool
    {
        $delete = new Delete();
        $delete->ExeDelete('conferencia_recebimento_historico', 'WHERE conferencia_id = :id', "id={$id}");
        $delete->ExeDelete('conferencia_recebimento', 'WHERE id = :id', "id={$id}");
        return (bool) $delete->getResult();
    }

    /**
     * Finalizar conferência
     */
    public function finalizarConferencia(int $id, array $dados): Update
    {
        $dados['status_conferencia'] = 'concluida';
        $dados['data_conferencia'] = date('Y-m-d H:i:s');
        
        $this->update = new Update();
        $this->update->ExeUpdate('conferencia_recebimento', $dados, "WHERE id = :id", "id={$id}");
        return $this->update;
    }

    /**
     * Obter estatísticas de conferência
     */
    public function getEstatisticasConferencia(): array
    {
        $this->read = new Read();
        
        // Total de conferências
        $this->read->FullRead("SELECT COUNT(*) as total FROM conferencia_recebimento");
        $total = $this->read->getResult();
        
        // Conferências pendentes
        $this->read->FullRead("SELECT COUNT(*) as pendentes FROM conferencia_recebimento WHERE status_conferencia = 'pendente'");
        $pendentes = $this->read->getResult();
        
        // Conferências em andamento
        $this->read->FullRead("SELECT COUNT(*) as em_andamento FROM conferencia_recebimento WHERE status_conferencia = 'em_andamento'");
        $emAndamento = $this->read->getResult();
        
        // Conferências concluídas
        $this->read->FullRead("SELECT COUNT(*) as concluidas FROM conferencia_recebimento WHERE status_conferencia = 'concluida'");
        $concluidas = $this->read->getResult();
        
        // Produtos aprovados
        $this->read->FullRead("SELECT COUNT(*) as aprovados FROM conferencia_recebimento WHERE status_qualidade = 'aprovado'");
        $aprovados = $this->read->getResult();
        
        // Produtos rejeitados
        $this->read->FullRead("SELECT COUNT(*) as rejeitados FROM conferencia_recebimento WHERE status_qualidade = 'reprovado'");
        $rejeitados = $this->read->getResult();
        
        return [
            'total' => $total[0]['total'] ?? 0,
            'pendentes' => $pendentes[0]['pendentes'] ?? 0,
            'em_andamento' => $emAndamento[0]['em_andamento'] ?? 0,
            'concluidas' => $concluidas[0]['concluidas'] ?? 0,
            'aprovados' => $aprovados[0]['aprovados'] ?? 0,
            'rejeitados' => $rejeitados[0]['rejeitados'] ?? 0
        ];
    }

    /**
     * Obter histórico de conferência
     */
    public function getHistoricoConferencia(int $conferenciaId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                crh.*,
                u.nome as usuario_nome
            FROM conferencia_recebimento_historico crh
            LEFT JOIN usuarios u ON crh.usuario_id = u.id
            WHERE crh.conferencia_id = :conferencia_id
            ORDER BY crh.data_acao DESC
        ", "conferencia_id={$conferenciaId}");
        return $this->read;
    }

    /**
     * Registrar ação no histórico
     */
    public function registrarHistorico(array $dados): Create
    {
        $this->create = new Create();
        $this->create->ExeCreate('conferencia_recebimento_historico', $dados);
        return $this->create;
    }

    /**
     * Verificar se NFE já foi conferida
     */
    public function nfeJaConferida(int $nfeId): bool
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total 
            FROM conferencia_recebimento 
            WHERE nfe_id = :nfe_id AND status_conferencia = 'concluida'
        ", "nfe_id={$nfeId}");
        
        $result = $this->read->getResult();
        return ($result[0]['total'] ?? 0) > 0;
    }

    /**
     * Obter resumo de conferência por NFE
     */
    public function getResumoConferenciaNfe(int $nfeId): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                COUNT(*) as total_itens,
                SUM(CASE WHEN status_conferencia = 'concluida' THEN 1 ELSE 0 END) as itens_conferidos,
                SUM(CASE WHEN status_qualidade = 'aprovado' THEN 1 ELSE 0 END) as itens_aprovados,
                SUM(CASE WHEN status_qualidade = 'reprovado' THEN 1 ELSE 0 END) as itens_rejeitados,
                SUM(quantidade_prevista) as total_previsto,
                SUM(quantidade_conferida) as total_conferido,
                SUM(CASE WHEN status_qualidade = 'aprovado' THEN quantidade_conferida ELSE 0 END) as total_aprovado
            FROM conferencia_recebimento 
            WHERE nfe_id = :nfe_id
        ", "nfe_id={$nfeId}");
        
        $result = $this->read->getResult();
        return $result[0] ?? [];
    }

    /**
     * Contagem por status de conferência (dashboard)
     */
    public function getCountByStatus(string $status): int
    {
        $this->read = new Read();

        if ($status === 'pendente') {
            $this->read->FullRead("
                SELECT COUNT(*) as total
                FROM conferencia_recebimento
                WHERE status_conferencia IN ('pendente', 'em_andamento')
            ");
        } else {
            $this->read->FullRead("
                SELECT COUNT(*) as total
                FROM conferencia_recebimento
                WHERE status_conferencia = :status
            ", "status={$status}");
        }

        $result = $this->read->getResult();
        return $result ? (int) $result[0]['total'] : 0;
    }

    /**
     * Estatísticas por qualidade para gráficos do dashboard
     */
    public function getEstatisticasPorQualidade(): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT status_qualidade, COUNT(*) as total
            FROM conferencia_recebimento
            WHERE status_qualidade IS NOT NULL AND status_qualidade != ''
            GROUP BY status_qualidade
            ORDER BY total DESC
        ");

        $result = $this->read->getResult();
        $estatisticas = [];

        if ($result) {
            foreach ($result as $row) {
                $estatisticas[$row['status_qualidade']] = (int) $row['total'];
            }
        }

        return $estatisticas;
    }

    /**
     * Conferências recentes formatadas para o dashboard
     */
    public function getRecentes(int $limit = 5): array
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT
                cr.*,
                COALESCE(nfe.numero_nfe, '-') as numero_nfe,
                COALESCE(p.nome, '-') as produto_nome,
                COALESCE(u.nome, '-') as usuario_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN usuarios u ON cr.usuario_conferente_id = u.id
            ORDER BY cr.data_conferencia DESC, cr.created_at DESC
            LIMIT :limit
        ", "limit={$limit}");

        $result = $this->read->getResult();
        if (!$result) {
            return [];
        }

        return array_map(function (array $row) {
            return [
                'id' => $row['id'],
                'produto' => $row['produto_nome'],
                'numero_nf' => $row['numero_nfe'],
                'quantidade_recebida' => (int) ($row['quantidade_conferida'] ?? $row['quantidade_recebida'] ?? 0),
                'quantidade_esperada' => (int) ($row['quantidade_prevista'] ?? 0),
                'status_qualidade' => $row['status_qualidade'] ?? 'pendente',
                'status_conferencia' => $row['status_conferencia'] ?? 'pendente',
                'data_conferencia' => $row['data_conferencia'] ?? $row['created_at'],
                'usuario_nome' => $row['usuario_nome'],
            ];
        }, $result);
    }

    /**
     * Relatório de conferências com filtros
     */
    public function gerarRelatorioConferencia(array $filtros = []): array
    {
        $this->read = new Read();
        $sql = "
            SELECT cr.*,
                   nfe.numero_nfe,
                   f.nome as fornecedor_nome,
                   p.nome as produto_nome,
                   p.SKU as produto_sku,
                   pv.tamanho,
                   c.nome as cor_nome,
                   u.nome as usuario_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN produtos_variations pv ON cr.variacao_id = pv.id
            LEFT JOIN cores c ON pv.cor = c.id
            LEFT JOIN usuarios u ON cr.usuario_conferente_id = u.id
            WHERE 1=1
        ";
        $params = '';

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(cr.data_conferencia) >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(cr.data_conferencia) <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }

        if (!empty($filtros['periodo']) && empty($filtros['data_inicio']) && empty($filtros['data_fim'])) {
            $dias = (int) $filtros['periodo'];
            if ($dias > 0) {
                $sql .= " AND cr.data_conferencia >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)";
            }
        }

        if (!empty($filtros['status_conferencia'])) {
            $sql .= " AND cr.status_conferencia = :status_conferencia";
            $params .= "status_conferencia={$filtros['status_conferencia']}&";
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND cr.status_conferencia = :status";
            $params .= "status={$filtros['status']}&";
        }

        if (!empty($filtros['status_qualidade'])) {
            $sql .= " AND cr.status_qualidade = :status_qualidade";
            $params .= "status_qualidade={$filtros['status_qualidade']}&";
        }

        if (!empty($filtros['fornecedor'])) {
            $sql .= " AND f.nome LIKE :fornecedor";
            $params .= "fornecedor=%{$filtros['fornecedor']}%&";
        }

        if (!empty($filtros['fornecedor_nome'])) {
            $sql .= " AND f.nome LIKE :fornecedor_nome";
            $params .= "fornecedor_nome=%{$filtros['fornecedor_nome']}%&";
        }

        if (!empty($filtros['conferente'])) {
            $sql .= " AND cr.usuario_conferente_id = :conferente";
            $params .= "conferente={$filtros['conferente']}&";
        }

        $sql .= " ORDER BY cr.data_conferencia DESC, cr.created_at DESC";

        $this->read->FullRead($sql, rtrim($params, '&'));
        $result = $this->read->getResult() ?: [];

        $headers = ['ID', 'NFE', 'Fornecedor', 'Produto', 'SKU', 'Qtd. Prevista', 'Qtd. Conferida', 'Status', 'Qualidade', 'Integridade', 'Data', 'Usuário'];
        $rows = [];

        foreach ($result as $row) {
            $rows[] = [
                $row['id'],
                $row['numero_nfe'] ?? '-',
                $row['fornecedor_nome'] ?? '-',
                $row['produto_nome'] ?? '-',
                $row['produto_sku'] ?? '-',
                $row['quantidade_prevista'] ?? 0,
                $row['quantidade_conferida'] ?? 0,
                $row['status_conferencia'] ?? '-',
                $row['status_qualidade'] ?? '-',
                $row['status_integridade'] ?? '-',
                $row['data_conferencia'] ?? '-',
                $row['usuario_nome'] ?? '-',
            ];
        }

        return [
            'dados' => $result,
            'headers' => $headers,
            'data' => $rows,
            'total_registros' => count($result),
            'total_conferencias' => count($result),
            'conferencias_aprovadas' => count(array_filter($result, fn($item) => ($item['status_qualidade'] ?? '') === 'aprovado')),
            'conferencias_rejeitadas' => count(array_filter($result, fn($item) => ($item['status_qualidade'] ?? '') === 'reprovado')),
            'periodo' => [
                'inicio' => $filtros['data_inicio'] ?? null,
                'fim' => $filtros['data_fim'] ?? null,
                'dias' => $filtros['periodo'] ?? null,
            ],
        ];
    }

    /**
     * Dados agregados para gráficos do relatório de conferência
     */
    public function getGraficosRelatorio(array $filtros = []): array
    {
        $relatorio = $this->gerarRelatorioConferencia($filtros);
        $dados = $relatorio['dados'];

        $status = ['pendente' => 0, 'em_andamento' => 0, 'concluida' => 0, 'cancelada' => 0];
        $qualidade = ['aprovado' => 0, 'reprovado' => 0, 'pendente' => 0];

        foreach ($dados as $row) {
            $st = $row['status_conferencia'] ?? 'pendente';
            if (isset($status[$st])) {
                $status[$st]++;
            }

            $q = $row['status_qualidade'] ?? 'pendente';
            if (isset($qualidade[$q])) {
                $qualidade[$q]++;
            }
        }

        return [
            'status' => $status,
            'qualidade' => $qualidade,
        ];
    }
}
