<?php

namespace Agencia\Close\Models\Transferencias;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;
use Exception;

class Transferencias extends Model
{
    private const MOTIVOS = ['reorganizacao', 'otimizacao_espaco', 'manutencao', 'outro'];

    public function getTransferencias(): Read
    {
        $read = new Read();
        $read->FullRead($this->sqlBase() . " ORDER BY t.data_solicitacao DESC");
        return $read;
    }

    public function getTransferencia($id): Read
    {
        $read = new Read();
        $read->FullRead($this->sqlBase() . " WHERE t.id = :id", "id={$id}");
        return $read;
    }

    public function getTransferenciasByArmazenagem(int $armazenagemId): Read
    {
        $read = new Read();
        $read->FullRead(
            $this->sqlBase() . " WHERE t.armazenagem_origem = :armazenagem_id OR t.armazenagem_destino = :armazenagem_id ORDER BY t.data_solicitacao DESC",
            "armazenagem_id={$armazenagemId}"
        );
        return $read;
    }

    public function getItensDisponiveis(): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT pnf.*,
                   a.codigo as armazenagem_codigo,
                   a.descricao as armazenagem_descricao
            FROM pedidos_nf pnf
            LEFT JOIN armazenagens a ON pnf.armazenagem_id = a.id
            WHERE pnf.armazenagem_id IS NOT NULL
            ORDER BY pnf.id DESC
        ");
        return $read;
    }

    public function createTransferencia($params): int|false
    {
        $itemId = (int)($params['item_id'] ?? 0);
        $origem = (int)($params['armazenagem_origem'] ?? 0);
        $destino = (int)($params['armazenagem_destino'] ?? 0);
        $quantidade = (int)($params['quantidade'] ?? 0);
        $motivo = $params['motivo'] ?? 'reorganizacao';

        if (!in_array($motivo, self::MOTIVOS, true)) {
            $motivo = 'outro';
        }

        $usuario = (int)($_SESSION[BASE . 'user_id'] ?? 0);
        if ($itemId <= 0 || $origem <= 0 || $destino <= 0 || $quantidade <= 0 || $origem === $destino || $usuario <= 0) {
            return false;
        }

        $create = new Create();
        $create->ExeCreate('armazenagem_transferencias', [
            'item_id' => $itemId,
            'armazenagem_origem' => $origem,
            'armazenagem_destino' => $destino,
            'quantidade' => $quantidade,
            'motivo' => $motivo,
            'observacoes' => trim($params['observacoes'] ?? '') ?: null,
            'usuario_solicitante' => $usuario,
            'status' => 'pendente',
        ]);

        $id = $create->getResult();
        return $id ? (int)$id : false;
    }

    public function executarTransferencia($id): bool
    {
        try {
            $read = new Read();
            $read->FullRead("SELECT * FROM armazenagem_transferencias WHERE id = :id", "id={$id}");
            $result = $read->getResult();
            $transferencia = $result[0] ?? null;

            if (!$transferencia || $transferencia['status'] !== 'pendente') {
                return false;
            }

            $read->FullRead("
                SELECT *
                FROM pedidos_nf
                WHERE id = :item_id
            ", "item_id={$transferencia['item_id']}");
            $item = $read->getResult()[0] ?? null;

            $quantidadeTransferida = (int)$transferencia['quantidade'];
            $quantidadeItem = (int)($item['quantidade'] ?? 0);
            $origemId = (int)$transferencia['armazenagem_origem'];
            $destinoId = (int)$transferencia['armazenagem_destino'];

            if (
                !$item
                || $quantidadeTransferida <= 0
                || $quantidadeItem < $quantidadeTransferida
                || (int)($item['armazenagem_id'] ?? 0) !== $origemId
            ) {
                return false;
            }

            $read->FullRead("
                SELECT id, capacidade_maxima, capacidade_atual
                FROM armazenagens
                WHERE id = :id
            ", "id={$transferencia['armazenagem_destino']}");
            $destino = $read->getResult()[0] ?? null;

            if (!$destino) {
                return false;
            }

            $capacidadeMaxima = $destino['capacidade_maxima'];
            if ($capacidadeMaxima !== null && $capacidadeMaxima !== '') {
                $ocupacao = (int)$destino['capacidade_atual'] + (int)$transferencia['quantidade'];
                if ($ocupacao > (int)$capacidadeMaxima) {
                    return false;
                }
            }

            $update = new Update();
            if ($quantidadeTransferida === $quantidadeItem) {
                $update->ExeUpdate(
                    'pedidos_nf',
                    ['armazenagem_id' => $destinoId],
                    'WHERE id = :id',
                    "id={$transferencia['item_id']}"
                );
            } else {
                $create = new Create();
                $create->ExeCreate('pedidos_nf', [
                    'nota_fiscal_id' => $item['nota_fiscal_id'],
                    'produto_id' => $item['produto_id'] ?? null,
                    'codigo_produto' => $item['codigo_produto'],
                    'descricao_produto' => $item['descricao_produto'],
                    'quantidade' => $quantidadeTransferida,
                    'quantidade_conferida' => $item['quantidade_conferida'] ?? null,
                    'unidade_medida' => $item['unidade_medida'] ?? 'UN',
                    'valor_unitario' => $item['valor_unitario'] ?? null,
                    'valor_total' => $item['valor_total'] ?? null,
                    'armazenagem_id' => $destinoId,
                    'status' => $item['status'] ?? 'alocado',
                    'observacoes' => $item['observacoes'] ?? null,
                ]);

                if (!$create->getResult()) {
                    return false;
                }

                $update->ExeUpdate(
                    'pedidos_nf',
                    ['quantidade' => $quantidadeItem - $quantidadeTransferida],
                    'WHERE id = :id',
                    "id={$transferencia['item_id']}"
                );
            }

            $update->ExeUpdate(
                'armazenagem_transferencias',
                [
                    'status' => 'concluida',
                    'usuario_executor' => $_SESSION[BASE . 'user_id'] ?? null,
                    'data_execucao' => date('Y-m-d H:i:s'),
                    'data_conclusao' => date('Y-m-d H:i:s'),
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
            $update->ExeUpdate(
                'armazenagem_transferencias',
                ['status' => 'cancelada'],
                'WHERE id = :id AND status = :status',
                "id={$id}&status=pendente"
            );
            return $update->getResult() === true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function gerarRelatorioTransferencias(array $filtros = []): array
    {
        $sql = $this->sqlBase() . " WHERE 1=1";
        $params = '';

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(t.data_solicitacao) >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(t.data_solicitacao) <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND t.status = :status";
            $params .= "status={$filtros['status']}&";
        }

        if (!empty($filtros['motivo'])) {
            $sql .= " AND t.motivo = :motivo";
            $params .= "motivo={$filtros['motivo']}&";
        }

        if (!empty($filtros['origem'])) {
            $sql .= " AND ao.codigo LIKE :origem";
            $params .= "origem=%{$filtros['origem']}%&";
        }

        if (!empty($filtros['destino'])) {
            $sql .= " AND ad.codigo LIKE :destino";
            $params .= "destino=%{$filtros['destino']}%&";
        }

        $sql .= " ORDER BY t.data_solicitacao DESC";

        $read = new Read();
        $read->FullRead($sql, rtrim($params, '&'));
        $result = $read->getResult() ?: [];

        foreach ($result as &$item) {
            $item['motivo_descricao'] = $this->descricaoMotivo($item['motivo'] ?? '');
            $item['status_descricao'] = $this->descricaoStatus($item['status'] ?? '');
        }
        unset($item);

        $headers = ['ID', 'Produto', 'Origem', 'Destino', 'Qtd', 'Motivo', 'Status', 'Solicitante', 'Executor', 'Solicitação', 'Conclusão'];
        $data = [];

        foreach ($result as $item) {
            $data[] = [
                $item['id'] ?? '',
                trim(($item['item_codigo'] ?? '') . ' ' . ($item['item_descricao'] ?? '')) ?: '-',
                $item['origem_codigo'] ?? '-',
                $item['destino_codigo'] ?? '-',
                $item['quantidade'] ?? 0,
                $this->descricaoMotivo($item['motivo'] ?? ''),
                $this->descricaoStatus($item['status'] ?? ''),
                $item['solicitante_nome'] ?? '-',
                $item['executor_nome'] ?? '-',
                $item['data_solicitacao'] ?? '-',
                $item['data_conclusao'] ?? '-',
            ];
        }

        return [
            'dados' => $result,
            'headers' => $headers,
            'data' => $data,
            'total_registros' => count($result),
            'total_concluidas' => count(array_filter($result, function ($item) {
                return ($item['status'] ?? '') === 'concluida';
            })),
            'total_pendentes' => count(array_filter($result, function ($item) {
                return ($item['status'] ?? '') === 'pendente';
            })),
            'total_quantidade' => array_sum(array_column($result, 'quantidade')),
            'periodo' => [
                'inicio' => $filtros['data_inicio'] ?? null,
                'fim' => $filtros['data_fim'] ?? null,
            ],
        ];
    }

    private function descricaoMotivo(string $motivo): string
    {
        $mapa = [
            'reorganizacao' => 'Reorganização',
            'otimizacao_espaco' => 'Otimização de espaço',
            'manutencao' => 'Manutenção',
            'outro' => 'Outro',
        ];

        return $mapa[$motivo] ?? $motivo;
    }

    private function descricaoStatus(string $status): string
    {
        $mapa = [
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
        ];

        return $mapa[$status] ?? $status;
    }

    private function sqlBase(): string
    {
        return "
            SELECT 
                t.*,
                pnf.codigo_produto as item_codigo,
                pnf.descricao_produto as item_descricao,
                ao.codigo as origem_codigo,
                ao.descricao as origem_nome,
                ad.codigo as destino_codigo,
                ad.descricao as destino_nome,
                us.nome as solicitante_nome,
                ue.nome as executor_nome
            FROM armazenagem_transferencias t
            LEFT JOIN pedidos_nf pnf ON t.item_id = pnf.id
            LEFT JOIN armazenagens ao ON t.armazenagem_origem = ao.id
            LEFT JOIN armazenagens ad ON t.armazenagem_destino = ad.id
            LEFT JOIN usuarios us ON t.usuario_solicitante = us.id
            LEFT JOIN usuarios ue ON t.usuario_executor = ue.id
        ";
    }
}
