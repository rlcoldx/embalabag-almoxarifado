<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Helpers\User\PermissionHelper;

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

        // TODO: Implementar lógica para buscar produtos armazenados
        return $this->jsonResponse([
            'success' => true,
            'produtos' => []
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

        // TODO: Implementar lógica para buscar estatísticas
        return $this->jsonResponse([
            'success' => true,
            'estatisticas' => []
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

        // Buscar movimentações da armazenagem
        $movimentacao = new \Agencia\Close\Models\Movimentacao\Movimentacao();
        $result = $movimentacao->getMovimentacoesByArmazenagem((int) $armazenagemId);

        if ($result->getResult()) {
            return $this->jsonResponse([
                'success' => true,
                'movimentacoes' => $result->getResult()
            ]);
        }

        return $this->jsonResponse([
            'success' => true,
            'movimentacoes' => []
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

        // Buscar transferências da armazenagem
        $transferencia = new \Agencia\Close\Models\Transferencias\Transferencias();
        $result = $transferencia->getTransferenciasByArmazenagem((int) $armazenagemId);

        if ($result->getResult()) {
            return $this->jsonResponse([
                'success' => true,
                'transferencias' => $result->getResult()
            ]);
        }

        return $this->jsonResponse([
            'success' => true,
            'transferencias' => []
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

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
