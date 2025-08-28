<?php

namespace Agencia\Close\Services\Home;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Produtos\Produtos;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Models\Recebimento\Recebimento;
use Agencia\Close\Models\Conferencia\Conferencia;
use Agencia\Close\Models\Movimentacao\Movimentacao;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Pedidos\Pedidos;

class HomeStatsService
{
    public function getDashboardStats(): array
    {
        return [
            'produtos' => $this->getProdutosStats(),
            'armazenagens' => $this->getArmazenagensStats(),
            'recebimentos' => $this->getRecebimentosStats(),
            'conferencias' => $this->getConferenciasStats(),
            'movimentacoes' => $this->getMovimentacoesStats(),
            'notas_fiscais' => $this->getNotasFiscaisStats(),
            'pedidos' => $this->getPedidosStats(),
            'usuarios' => $this->getUsuariosStats(),
            'estoque' => $this->getEstoqueStats(),
            'recentes' => $this->getAtividadesRecentes()
        ];
    }

    private function getProdutosStats(): array
    {
        $read = new Read();
        
        // Total de produtos
        $read->ExeRead("produtos", "WHERE status <> 'Deletado'");
        $totalProdutos = $read->getRowCount();
        
        // Produtos ativos
        $read->ExeRead("produtos", "WHERE status = 'Publicado'");
        $produtosAtivos = $read->getRowCount();
        
        // Produtos rascunho
        $read->ExeRead("produtos", "WHERE status = 'Rascunho'");
        $produtosRascunho = $read->getRowCount();
        
        // Produtos com estoque baixo (usando campo 'estoque' da tabela produtos_variations)
        $read->FullRead("SELECT COUNT(*) as total FROM produtos_variations WHERE estoque <= 10 AND estoque > 0");
        $estoqueBaixo = $read->getResult()[0]['total'] ?? 0;
        
        // Produtos sem estoque
        $read->ExeRead("produtos_variations", "WHERE estoque = 0 OR estoque IS NULL");
        $semEstoque = $read->getRowCount();
        
        return [
            'total' => $totalProdutos,
            'ativos' => $produtosAtivos,
            'rascunho' => $produtosRascunho,
            'estoque_baixo' => $estoqueBaixo,
            'sem_estoque' => $semEstoque
        ];
    }

    private function getArmazenagensStats(): array
    {
        $read = new Read();
        
        // Total de armazenagens
        $read->ExeRead("armazenagens");
        $totalArmazenagens = $read->getRowCount();
        
        // Capacidade total (usando campo 'capacidade_maxima')
        $read->FullRead("SELECT SUM(capacidade_maxima) as capacidade_total FROM armazenagens WHERE status = 'ativo'");
        $capacidadeTotal = $read->getResult()[0]['capacidade_total'] ?? 0;
        
        // Capacidade utilizada (usando campo 'capacidade_atual')
        $read->FullRead("SELECT SUM(capacidade_atual) as capacidade_utilizada FROM armazenagens WHERE status = 'ativo'");
        $capacidadeUtilizada = $read->getResult()[0]['capacidade_utilizada'] ?? 0;
        
        // Percentual de ocupação
        $percentualOcupacao = $capacidadeTotal > 0 ? round(($capacidadeUtilizada / $capacidadeTotal) * 100, 1) : 0;
        
        // Top 5 armazéns mais lotados
        $read->FullRead("SELECT 
            a.codigo as nome_armazem,
            a.tipo as tipo_armazem,
            a.setor as setor_armazem,
            a.capacidade_maxima as capacidade_total,
            a.capacidade_atual as capacidade_utilizada,
            CASE 
                WHEN a.capacidade_maxima > 0 THEN ROUND((a.capacidade_atual / a.capacidade_maxima) * 100, 1)
                ELSE 0 
            END as percentual_ocupacao
            FROM armazenagens a
            WHERE a.status = 'ativo' AND a.capacidade_atual > 0
            ORDER BY percentual_ocupacao DESC
            LIMIT 5");
        $topArmazenagens = $read->getResult();
        
        return [
            'total' => $totalArmazenagens,
            'capacidade_total' => $capacidadeTotal,
            'capacidade_utilizada' => $capacidadeUtilizada,
            'percentual_ocupacao' => $percentualOcupacao,
            'top_5_lotados' => $topArmazenagens
        ];
    }

    private function getRecebimentosStats(): array
    {
        $read = new Read();
        
        // Como não existe tabela 'recebimentos', vamos usar 'notas_fiscais' como base
        // Total de notas fiscais (que representam recebimentos)
        $read->ExeRead("notas_fiscais");
        $totalRecebimentos = $read->getRowCount();
        
        // Recebimentos hoje (notas fiscais recebidas hoje)
        $read->FullRead("SELECT COUNT(*) as total FROM notas_fiscais WHERE DATE(data_recebimento) = CURDATE()");
        $recebimentosHoje = $read->getResult()[0]['total'] ?? 0;
        
        // Recebimentos este mês
        $read->FullRead("SELECT COUNT(*) as total FROM notas_fiscais WHERE MONTH(data_recebimento) = MONTH(CURDATE()) AND YEAR(data_recebimento) = YEAR(CURDATE())");
        $recebimentosMes = $read->getResult()[0]['total'] ?? 0;
        
        // Recebimentos pendentes
        $read->ExeRead("notas_fiscais", "WHERE status = 'pendente'");
        $recebimentosPendentes = $read->getRowCount();
        
        return [
            'total' => $totalRecebimentos,
            'hoje' => $recebimentosHoje,
            'este_mes' => $recebimentosMes,
            'pendentes' => $recebimentosPendentes
        ];
    }

    private function getConferenciasStats(): array
    {
        $read = new Read();
        
        // Total de conferências
        $read->ExeRead("conferencia_produtos");
        $totalConferencias = $read->getRowCount();
        
        // Conferências hoje
        $read->FullRead("SELECT COUNT(*) as total FROM conferencia_produtos WHERE DATE(data_conferencia) = CURDATE()");
        $conferenciasHoje = $read->getResult()[0]['total'] ?? 0;
        
        // Conferências este mês
        $read->FullRead("SELECT COUNT(*) as total FROM conferencia_produtos WHERE MONTH(data_conferencia) = MONTH(CURDATE()) AND YEAR(data_conferencia) = YEAR(CURDATE())");
        $conferenciasMes = $read->getResult()[0]['total'] ?? 0;
        
        // Conferências pendentes (não há campo status, então vamos considerar todas como concluídas)
        $conferenciasPendentes = 0;
        
        return [
            'total' => $totalConferencias,
            'hoje' => $conferenciasHoje,
            'este_mes' => $conferenciasMes,
            'pendentes' => $conferenciasPendentes
        ];
    }

    private function getMovimentacoesStats(): array
    {
        $read = new Read();
        
        // Total de movimentações
        $read->ExeRead("movimentacoes");
        $totalMovimentacoes = $read->getRowCount();
        
        // Movimentações hoje
        $read->FullRead("SELECT COUNT(*) as total FROM movimentacoes WHERE DATE(created_at) = CURDATE()");
        $movimentacoesHoje = $read->getResult()[0]['total'] ?? 0;
        
        // Movimentações este mês
        $read->FullRead("SELECT COUNT(*) as total FROM movimentacoes WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
        $movimentacoesMes = $read->getResult()[0]['total'] ?? 0;
        
        // Entradas vs Saídas (usando campo 'tipo' correto)
        $read->FullRead("SELECT 
            SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END) as saidas
            FROM movimentacoes");
        $result = $read->getResult()[0] ?? ['entradas' => 0, 'saidas' => 0];
        
        return [
            'total' => $totalMovimentacoes,
            'hoje' => $movimentacoesHoje,
            'este_mes' => $movimentacoesMes,
            'entradas' => $result['entradas'] ?? 0,
            'saidas' => $result['saidas'] ?? 0
        ];
    }

    private function getNotasFiscaisStats(): array
    {
        $read = new Read();
        
        // Total de notas fiscais
        $read->ExeRead("notas_fiscais");
        $totalNFs = $read->getRowCount();
        
        // NFs este mês
        $read->FullRead("SELECT COUNT(*) as total FROM notas_fiscais WHERE MONTH(data_emissao) = MONTH(CURDATE()) AND YEAR(data_emissao) = YEAR(CURDATE())");
        $nfsMes = $read->getResult()[0]['total'] ?? 0;
        
        // Valor total das NFs
        $read->FullRead("SELECT SUM(valor_total) as valor_total FROM notas_fiscais");
        $valorTotal = $read->getResult()[0]['valor_total'] ?? 0;
        
        // NFs pendentes
        $read->ExeRead("notas_fiscais", "WHERE status = 'pendente'");
        $nfsPendentes = $read->getRowCount();
        
        return [
            'total' => $totalNFs,
            'este_mes' => $nfsMes,
            'valor_total' => $valorTotal,
            'pendentes' => $nfsPendentes
        ];
    }

    private function getPedidosStats(): array
    {
        $read = new Read();
        
        // Total de pedidos
        $read->ExeRead("pedidos");
        $totalPedidos = $read->getRowCount();
        
        // Pedidos este mês
        $read->FullRead("SELECT COUNT(*) as total FROM pedidos WHERE MONTH(data_pedido) = MONTH(CURDATE()) AND YEAR(data_pedido) = YEAR(CURDATE())");
        $pedidosMes = $read->getResult()[0]['total'] ?? 0;
        
        // Pedidos pendentes
        $read->ExeRead("pedidos", "WHERE status = 'pendente'");
        $pedidosPendentes = $read->getRowCount();
        
        // Pedidos aprovados
        $read->ExeRead("pedidos", "WHERE status = 'aprovado'");
        $pedidosAprovados = $read->getRowCount();
        
        return [
            'total' => $totalPedidos,
            'este_mes' => $pedidosMes,
            'pendentes' => $pedidosPendentes,
            'aprovados' => $pedidosAprovados
        ];
    }

    private function getUsuariosStats(): array
    {
        $read = new Read();
        
        // Total de usuários
        $read->ExeRead("usuarios");
        $totalUsuarios = $read->getRowCount();
        
        // Usuários ativos
        $read->ExeRead("usuarios", "WHERE status = 'ativo'");
        $usuariosAtivos = $read->getRowCount();
        
        // Usuários online (últimas 24h) - usando campo 'data_acesso' correto
        $read->FullRead("SELECT COUNT(*) as total FROM log_acessos WHERE data_acesso >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'sucesso'");
        $usuariosOnline = $read->getResult()[0]['total'] ?? 0;
        
        return [
            'total' => $totalUsuarios,
            'ativos' => $usuariosAtivos,
            'online' => $usuariosOnline
        ];
    }

    private function getEstoqueStats(): array
    {
        $read = new Read();
        
        // Valor total do estoque (usando campo 'estoque' e 'valor' corretos)
        $read->FullRead("SELECT SUM(pv.estoque * COALESCE(CAST(p.valor AS DECIMAL(10,2)), 0)) as valor_total 
                        FROM produtos_variations pv 
                        LEFT JOIN produtos p ON pv.id_produto = p.id 
                        WHERE pv.estoque > 0");
        $valorEstoque = $read->getResult()[0]['valor_total'] ?? 0;
        
        // Produtos com estoque crítico (estoque <= 10)
        $read->FullRead("SELECT COUNT(*) as total FROM produtos_variations WHERE estoque <= 10 AND estoque > 0");
        $estoqueCritico = $read->getResult()[0]['total'] ?? 0;
        
        // Produtos sem estoque
        $read->ExeRead("produtos_variations", "WHERE estoque = 0 OR estoque IS NULL");
        $semEstoque = $read->getRowCount();
        
        return [
            'valor_total' => $valorEstoque,
            'critico' => $estoqueCritico,
            'sem_estoque' => $semEstoque
        ];
    }

    private function getAtividadesRecentes(): array
    {
        $read = new Read();
        
        // Últimas movimentações
        $read->FullRead("SELECT m.*, p.nome as produto_nome, u.nome as usuario_nome 
                        FROM movimentacoes m 
                        LEFT JOIN produtos p ON m.produto_id = p.id 
                        LEFT JOIN usuarios u ON m.usuario_id = u.id 
                        ORDER BY m.created_at DESC LIMIT 5");
        $movimentacoesRecentes = $read->getResult();
        
        // Últimos recebimentos (usando notas fiscais)
        $read->FullRead("SELECT nf.*, u.nome as usuario_nome 
                        FROM notas_fiscais nf 
                        LEFT JOIN usuarios u ON nf.usuario_recebimento = u.id 
                        ORDER BY nf.data_recebimento DESC LIMIT 5");
        $recebimentosRecentes = $read->getResult();
        
        // Últimas conferências
        $read->FullRead("SELECT c.*, u.nome as usuario_nome 
                        FROM conferencia_produtos c 
                        LEFT JOIN usuarios u ON c.usuario_conferente = u.id 
                        ORDER BY c.data_conferencia DESC LIMIT 5");
        $conferenciasRecentes = $read->getResult();
        
        return [
            'movimentacoes' => $movimentacoesRecentes,
            'recebimentos' => $recebimentosRecentes,
            'conferencias' => $conferenciasRecentes
        ];
    }
}
