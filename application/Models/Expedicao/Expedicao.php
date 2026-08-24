<?php

namespace Agencia\Close\Models\Expedicao;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Expedicao extends Model
{
    public const STATUS = [
        'aprovado' => 'Aprovado',
        'em_separacao' => 'Em separação',
        'separado' => 'Separado',
        'em_embalagem' => 'Em embalagem',
        'embalado' => 'Embalado',
        'conferido' => 'Conferido',
        'romaneio' => 'Em romaneio',
        'enviado' => 'Enviado',
    ];

    public function getPedidosAprovados(): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT p.*, u.nome as usuario_nome, c.nome as companhia_nome, c.companhia as companhia_empresa
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            LEFT JOIN usuarios c ON p.companhia_id = c.id
            WHERE p.status_pedido = '2'
              AND (p.status_expedicao IS NULL OR p.status_expedicao = '' OR p.status_expedicao = 'aprovado')
            ORDER BY p.prioridade DESC, p.date_create DESC
        ");
        return $read->getResult() ?: [];
    }

    public function getPedidosPorEtapa(string $status): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT p.*, u.nome as usuario_nome, c.nome as companhia_nome, c.companhia as companhia_empresa
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            LEFT JOIN usuarios c ON p.companhia_id = c.id
            WHERE p.status_expedicao = :status
            ORDER BY p.date_create DESC
        ", "status={$status}");
        return $read->getResult() ?: [];
    }

    public function getPedidoCompleto(int $id): ?array
    {
        $read = new Read();
        $read->FullRead("
            SELECT p.*, u.nome as usuario_nome, c.nome as companhia_nome, c.companhia as companhia_empresa
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            LEFT JOIN usuarios c ON p.companhia_id = c.id
            WHERE p.id = :id
        ", "id={$id}");
        $rows = $read->getResult();
        return $rows[0] ?? null;
    }

    public function atualizarStatus(int $pedidoId, string $status, array $extra = []): bool
    {
        $data = array_merge(['status_expedicao' => $status], $extra);
        $update = new Update();
        $update->ExeUpdate('pedidos', $data, 'WHERE id = :id', "id={$pedidoId}");
        return true;
    }

    public function iniciarSeparacao(int $pedidoId, int $usuarioId): bool
    {
        $read = new Read();
        $read->FullRead("SELECT COUNT(*) as total FROM pedido_separacao WHERE pedido_id = :id", "id={$pedidoId}");
        $total = (int)(($read->getResult()[0]['total'] ?? 0));

        if ($total === 0) {
            $read->FullRead("SELECT id_produto, qty FROM pedidos_itens WHERE id_pedido = :id", "id={$pedidoId}");
            $itens = $read->getResult() ?: [];
            $create = new Create();
            foreach ($itens as $item) {
                $origem = $this->primeiroEnderecoProduto((int)$item['id_produto']);
                $create->ExeCreate('pedido_separacao', [
                    'pedido_id' => $pedidoId,
                    'produto_id' => $item['id_produto'],
                    'quantidade' => $item['qty'] ?? 1,
                    'armazenagem_origem_id' => $origem,
                    'status' => 'pendente',
                    'usuario_id' => $usuarioId,
                ]);
            }
        }

        return $this->atualizarStatus($pedidoId, 'em_separacao');
    }

    public function getItensSeparacao(int $pedidoId): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT s.*, p.nome as produto_nome, p.SKU as produto_sku,
                   ao.codigo as endereco_origem, an.codigo as endereco_novo
            FROM pedido_separacao s
            LEFT JOIN produtos p ON p.id = s.produto_id
            LEFT JOIN armazenagens ao ON ao.id = s.armazenagem_origem_id
            LEFT JOIN armazenagens an ON an.id = s.armazenagem_nova_id
            WHERE s.pedido_id = :id
            ORDER BY s.id ASC
        ", "id={$pedidoId}");
        return $read->getResult() ?: [];
    }

    public function marcarItemSeparado(int $itemId, int $usuarioId): bool
    {
        $update = new Update();
        $update->ExeUpdate('pedido_separacao', [
            'status' => 'separado',
            'usuario_id' => $usuarioId,
        ], 'WHERE id = :id', "id={$itemId}");
        return true;
    }

    public function trocarEndereco(int $itemId, int $novoEnderecoId): bool
    {
        $update = new Update();
        $update->ExeUpdate('pedido_separacao', [
            'armazenagem_nova_id' => $novoEnderecoId,
        ], 'WHERE id = :id', "id={$itemId}");
        return true;
    }

    public function separacaoCompleta(int $pedidoId): bool
    {
        $read = new Read();
        $read->FullRead("SELECT COUNT(*) as total, SUM(status = 'separado') as separados FROM pedido_separacao WHERE pedido_id = :id", "id={$pedidoId}");
        $row = $read->getResult()[0] ?? ['total' => 0, 'separados' => 0];
        return (int)$row['total'] > 0 && (int)$row['total'] === (int)$row['separados'];
    }

    public function registrarConferencia(int $pedidoId, string $tipo, string $codigo, int $usuarioId): bool
    {
        $create = new Create();
        $create->ExeCreate('pedido_conferencia_saida', [
            'pedido_id' => $pedidoId,
            'tipo' => $tipo,
            'codigo_lido' => $codigo,
            'conferido' => 1,
            'usuario_id' => $usuarioId,
        ]);
        return !empty($create->getResult());
    }

    public function getConferencias(int $pedidoId): array
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM pedido_conferencia_saida WHERE pedido_id = :id ORDER BY id DESC", "id={$pedidoId}");
        return $read->getResult() ?: [];
    }

    public function getEncomendas(): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT pi.*, p.nome as produto_nome, p.SKU as produto_sku,
                   ped.codigo as pedido_codigo, ped.id as pedido_id,
                   ped.previsao_entrega, ped.status_pedido, ped.nome_cliente
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON p.id = pi.id_produto
            LEFT JOIN pedidos ped ON ped.id = pi.id_pedido
            WHERE pi.encomenda = 'yes'
            ORDER BY COALESCE(pi.previsao_chegada, ped.previsao_entrega) ASC
        ");
        return $read->getResult() ?: [];
    }

    public function atualizarPrevisaoEncomenda(int $pedidoId, int $produtoId, string $previsao): bool
    {
        $update = new Update();
        $update->ExeUpdate('pedidos_itens', [
            'previsao_chegada' => $previsao,
        ], 'WHERE id_pedido = :pedido AND id_produto = :produto', "pedido={$pedidoId}&produto={$produtoId}");
        $update->ExeUpdate('pedidos', [
            'previsao_entrega' => $previsao,
        ], 'WHERE id = :id', "id={$pedidoId}");
        return true;
    }

    public function criarAlertaOf(int $pedidoId, ?int $notaFiscalId, string $mensagem): void
    {
        $create = new Create();
        $create->ExeCreate('pedido_alertas', [
            'pedido_id' => $pedidoId,
            'nota_fiscal_id' => $notaFiscalId,
            'mensagem' => $mensagem,
            'lido' => 0,
        ]);
    }

    public function getEncomendasAtrasadas(): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT pi.*, p.nome as produto_nome, ped.codigo as pedido_codigo, ped.id as pedido_id,
                   COALESCE(pi.previsao_chegada, ped.previsao_entrega) as previsao
            FROM pedidos_itens pi
            LEFT JOIN produtos p ON p.id = pi.id_produto
            LEFT JOIN pedidos ped ON ped.id = pi.id_pedido
            WHERE pi.encomenda = 'yes'
              AND COALESCE(pi.previsao_chegada, ped.previsao_entrega) IS NOT NULL
              AND COALESCE(pi.previsao_chegada, ped.previsao_entrega) <> ''
              AND STR_TO_DATE(COALESCE(pi.previsao_chegada, ped.previsao_entrega), '%Y-%m-%d') < CURDATE()
            LIMIT 20
        ");
        return $read->getResult() ?: [];
    }

    public function getAlertasAbertos(): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT a.*, p.codigo as pedido_codigo, nf.numero as nf_numero
            FROM pedido_alertas a
            LEFT JOIN pedidos p ON p.id = a.pedido_id
            LEFT JOIN notas_fiscais nf ON nf.id = a.nota_fiscal_id
            WHERE a.lido = 0
            ORDER BY a.created_at DESC
            LIMIT 20
        ");
        return $read->getResult() ?: [];
    }

    public function getHistoricoProduto(int $produtoId): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT m.data_movimentacao, m.tipo, m.documento_referencia,
                   ao.codigo as endereco_origem, ad.codigo as endereco_destino,
                   m.quantidade, p.codigo as pedido_codigo
            FROM movimentacoes_historico m
            LEFT JOIN armazenagens ao ON ao.id = m.armazenagem_origem_id
            LEFT JOIN armazenagens ad ON ad.id = m.armazenagem_destino_id
            LEFT JOIN pedidos p ON p.codigo = m.documento_referencia OR CAST(p.id AS CHAR) = m.documento_referencia
            WHERE CAST(m.id_produto AS UNSIGNED) = :id
            ORDER BY m.data_movimentacao DESC
            LIMIT 80
        ", "id={$produtoId}");
        return $read->getResult() ?: [];
    }

    public function getCompanhias(): array
    {
        $read = new Read();
        $read->FullRead("SELECT id, nome, companhia FROM usuarios WHERE tipo = '3' AND status = 'ativo' ORDER BY nome");
        return $read->getResult() ?: [];
    }

    private function primeiroEnderecoProduto(int $produtoId): ?int
    {
        $read = new Read();
        $read->FullRead("
            SELECT armazenagem_id
            FROM estoque
            WHERE CAST(id_produto AS UNSIGNED) = :id AND status = 'ativo' AND quantidade > 0
            ORDER BY quantidade DESC
            LIMIT 1
        ", "id={$produtoId}");
        $row = $read->getResult()[0] ?? null;
        return $row ? (int)$row['armazenagem_id'] : null;
    }
}
