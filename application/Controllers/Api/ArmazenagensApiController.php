<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Conn\Read;

class ArmazenagensApiController extends Controller
{
    public function listar()
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagem = new Armazenagem();
        $result = $armazenagem->getAllArmazenagens();

        if ($result->getResult()) {
            return $this->jsonResponse([
                'success' => true,
                'armazenagens' => $result->getResult()
            ]);
        }

        return $this->jsonResponse(['success' => false, 'message' => 'Nenhuma armazenagem encontrada']);
    }

    public function getProdutosArmazenados($params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            return $this->jsonResponse(['success' => false, 'message' => 'ID da armazenagem não informado']);
        }

        // Buscar produtos armazenados (apenas estoque ativo)
        $armazenagem = new Armazenagem();
        $result = $armazenagem->getProdutosArmazenados((int) $armazenagemId);

        return $this->jsonResponse([
            'success' => true,
            'produtos' => $result->getResult() ?? []
        ]);
    }

    public function getEstatisticas($params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            return $this->jsonResponse(['success' => false, 'message' => 'ID da armazenagem não informado']);
        }

        // Buscar estatísticas da armazenagem (apenas estoque ativo)
        $armazenagem = new Armazenagem();
        $estatisticas = $armazenagem->getEstatisticasArmazenagem((int) $armazenagemId);

        return $this->jsonResponse([
            'success' => true,
            'estatisticas' => $estatisticas
        ]);
    }

    public function getMovimentacoes($params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            return $this->jsonResponse(['success' => false, 'message' => 'ID da armazenagem não informado']);
        }

        // Buscar movimentações da armazenagem do histórico
        $armazenagem = new Armazenagem();
        $result = $armazenagem->getMovimentacoesByArmazenagem((int) $armazenagemId);

        $movimentacoes = $result->getResult() ?? [];
        
        // Formatar dados para o frontend
        $movimentacoesFormatadas = array_map(function($mov) {
            return [
                'id' => $mov['id'],
                'tipo' => $mov['tipo'],
                'data_movimentacao' => $mov['data_movimentacao'],
                'produto_descricao' => $mov['produto_nome'] . ' - ' . $mov['produto_cor'],
                'produto_sku' => $mov['produto_sku'],
                'quantidade' => $mov['quantidade'],
                'motivo' => $mov['motivo'] ?? '-',
                'observacao' => $mov['observacoes'] ?? '-',
                'usuario_nome' => $mov['usuario_nome'] ?? 'Sistema'
            ];
        }, $movimentacoes);

        return $this->jsonResponse([
            'success' => true,
            'movimentacoes' => $movimentacoesFormatadas
        ]);
    }

    public function getTransferencias($params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            return $this->jsonResponse(['success' => false, 'message' => 'ID da armazenagem não informado']);
        }

        // Buscar movimentações de transferência (entradas e saídas com motivo de transferência)
        $read = new Read();
        $read->FullRead("
            SELECT 
                m.*,
                p.nome as produto_nome,
                p.SKU as produto_sku,
                COALESCE(pv.cor, 'Sem Variação') as produto_cor,
                u.nome as usuario_nome,
                ao.codigo as origem_codigo,
                ad.codigo as destino_codigo
            FROM movimentacoes_historico m
            INNER JOIN produtos p ON CAST(m.id_produto AS UNSIGNED) = p.id
            LEFT JOIN produtos_variations pv ON pv.id = m.variacao_id
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN armazenagens ao ON ao.id = m.armazenagem_origem_id
            LEFT JOIN armazenagens ad ON ad.id = m.armazenagem_destino_id
            WHERE m.armazenagem_origem_id = :armazenagem_id
              AND m.motivo IN ('reorganizacao', 'otimizacao_espaco', 'manutencao', 'inventario')
              AND (m.tipo = 'entrada' OR m.tipo = 'saida')
            ORDER BY m.data_movimentacao DESC
            LIMIT 100
        ", "armazenagem_id={$armazenagemId}");

        $transferencias = $read->getResult() ?? [];
        
        // Formatar dados para o frontend
        $transferenciasFormatadas = [];
        $transferenciasAgrupadas = [];
        
        // Formatar transferências
        foreach ($transferencias as $transf) {
            // Converter motivo para texto legível
            $motivos = [
                'reorganizacao' => 'Reorganização',
                'otimizacao_espaco' => 'Otimização de Espaço',
                'manutencao' => 'Manutenção',
                'inventario' => 'Inventário'
            ];
            
            $motivoTexto = $motivos[$transf['motivo']] ?? $transf['motivo'];
            
            // Usar os códigos das armazenagens dos JOINs
            $origem = $transf['origem_codigo'] ?? 'N/A';
            $destino = $transf['destino_codigo'] ?? 'N/A';
            
            $transferenciasFormatadas[] = [
                'id' => $transf['id'],
                'data_solicitacao' => $transf['data_movimentacao'],
                'item_descricao' => $transf['produto_nome'] . ' - ' . $transf['produto_cor'],
                'quantidade' => $transf['quantidade'],
                'origem_codigo' => $origem,
                'destino_codigo' => $destino,
                'motivo' => $motivoTexto,
                'observacoes' => $transf['observacoes'],
                'solicitante_nome' => $transf['usuario_nome'] ?? 'Sistema',
                'tipo_movimentacao' => ucfirst($transf['tipo']) // Adicionar tipo para identificação
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'transferencias' => $transferenciasFormatadas
        ]);
    }

    public function getHistorico($params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            return $this->jsonResponse(['success' => false, 'message' => 'ID da armazenagem não informado']);
        }

        // Buscar movimentações da armazenagem
        $movimentacao = new \Agencia\Close\Models\Movimentacao\Movimentacao();
        $resultMovimentacoes = $movimentacao->getMovimentacoesByArmazenagem((int) $armazenagemId);

        // Buscar transferências da armazenagem
        $transferencia = new \Agencia\Close\Models\Transferencias\Transferencias();
        $resultTransferencias = $transferencia->getTransferenciasByArmazenagem((int) $armazenagemId);

        $historico = [];

        // Adicionar movimentações ao histórico
        if ($resultMovimentacoes->getResult()) {
            foreach ($resultMovimentacoes->getResult() as $mov) {
                $historico[] = [
                    'tipo' => 'movimentacao',
                    'titulo' => ucfirst($mov['tipo_movimentacao']) . ' de Produto',
                    'descricao' => "{$mov['quantidade']} unidades de {$mov['produto_descricao']}",
                    'data' => $mov['data_movimentacao'],
                    'usuario' => $mov['usuario_nome']
                ];
            }
        }

        // Adicionar transferências ao histórico
        if ($resultTransferencias->getResult()) {
            foreach ($resultTransferencias->getResult() as $transf) {
                $historico[] = [
                    'tipo' => 'transferencia',
                    'titulo' => 'Transferência de Produto',
                    'descricao' => "{$transf['quantidade']} unidades de {$transf['item_descricao']}",
                    'data' => $transf['data_solicitacao'],
                    'usuario' => $transf['solicitante_nome']
                ];
            }
        }

        // Ordenar por data (mais recente primeiro)
        usort($historico, function($a, $b) {
            return strtotime($b['data']) - strtotime($a['data']);
        });

        return $this->jsonResponse([
            'success' => true,
            'historico' => $historico
        ]);
    }

    public function getEstoque($params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagemId = $params['id'] ?? null;
        $produtoId = $_GET['produto_id'] ?? null;
        $variacaoId = $_GET['variacao_id'] ?? null;

        if (!$armazenagemId || !$produtoId || !$variacaoId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Parâmetros incompletos']);
        }

        // Buscar estoque do produto/variação na armazenagem
        $read = new Read();
        $read->FullRead("
            SELECT quantidade, status
            FROM estoque
            WHERE armazenagem_id = :armazenagem_id
              AND id_produto = :produto_id
              AND variacao_id = :variacao_id
              AND status = 'ativo'
        ", "armazenagem_id={$armazenagemId}&produto_id={$produtoId}&variacao_id={$variacaoId}");

        $estoque = $read->getResult();

        if ($estoque && count($estoque) > 0) {
            return $this->jsonResponse([
                'success' => true,
                'estoque' => [
                    'quantidade' => $estoque[0]['quantidade'],
                    'status' => $estoque[0]['status']
                ]
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Produto não encontrado nesta armazenagem'
        ]);
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
