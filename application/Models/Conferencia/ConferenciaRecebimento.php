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
                COALESCE(nfe.fornecedor_nome, '-') as fornecedor_nome,
                COALESCE(p.nome, '-') as produto_nome,
                COALESCE(p.SKU, '-') as produto_sku,
                COALESCE(pv.tamanho, '-') as tamanho,
                COALESCE(c.nome, '-') as cor_nome,
                COALESCE(u.nome, '-') as usuario_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
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
                nfe.fornecedor_nome,
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
                nfe.fornecedor_nome,
                p.nome as produto_nome,
                p.SKU as produto_sku,
                pv.tamanho,
                c.nome as cor_nome
            FROM conferencia_recebimento cr
            LEFT JOIN notas_fiscais_eletronicas nfe ON cr.nfe_id = nfe.id
            LEFT JOIN produtos p ON cr.produto_id = p.id
            LEFT JOIN produtos_variations pv ON cr.variacao_id = pv.id
            LEFT JOIN cores c ON pv.cor = c.id
            WHERE cr.status_conferencia IN ('pendente', 'em_andamento')
            ORDER BY cr.created_at ASC
        ");
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
}
