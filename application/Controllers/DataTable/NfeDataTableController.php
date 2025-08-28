<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Recebimento\NotaFiscalEletronica;
use Agencia\Close\Helpers\DataTable\DataTableHelper;

class NfeDataTableController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new \Agencia\Close\Helpers\User\PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para acessar este módulo'
            ]);
            return;
        }
        
        $nfe = new NotaFiscalEletronica();
        
        // Aplicar filtros
        $filtros = $this->aplicarFiltros($nfe);
        
        // Buscar dados
        $result = $nfe->getAllWithDetails();
        $dados = $result->getResult() ?? [];
        
        // Aplicar filtros manuais se necessário
        if (!empty($filtros)) {
            $dados = $this->filtrarDados($dados, $filtros);
        }
        
        // Paginação
        $page = (int)($_POST['start'] ?? 0) / (int)($_POST['length'] ?? 10) + 1;
        $limit = (int)($_POST['length'] ?? 10);
        $total = count($dados);
        
        $dados = array_slice($dados, ($page - 1) * $limit, $limit);
        
        // Formatar dados para DataTable
        $dadosFormatados = $this->formatarDados($dados);
        
        $this->responseJson([
            'draw' => (int)($_POST['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $dadosFormatados
        ]);
    }
    
    private function aplicarFiltros($nfe)
    {
        $filtros = [];
        
        // Status
        if (!empty($_POST['status'])) {
            $filtros['status'] = $_POST['status'];
        }
        
        // Fornecedor
        if (!empty($_POST['fornecedor'])) {
            $filtros['fornecedor_id'] = $_POST['fornecedor'];
        }
        
        // Data início
        if (!empty($_POST['data_inicio'])) {
            $filtros['data_inicio'] = $_POST['data_inicio'];
        }
        
        // Data fim
        if (!empty($_POST['data_fim'])) {
            $filtros['data_fim'] = $_POST['data_fim'];
        }
        
        return $filtros;
    }
    
    private function filtrarDados($dados, $filtros)
    {
        return array_filter($dados, function($item) use ($filtros) {
            // Status
            if (isset($filtros['status']) && $item['status'] !== $filtros['status']) {
                return false;
            }
            
            // Fornecedor
            if (isset($filtros['fornecedor_id']) && $item['fornecedor_id'] != $filtros['fornecedor_id']) {
                return false;
            }
            
            // Data início
            if (isset($filtros['data_inicio']) && $item['data_recebimento'] < $filtros['data_inicio']) {
                return false;
            }
            
            // Data fim
            if (isset($filtros['data_fim']) && $item['data_recebimento'] > $filtros['data_fim']) {
                return false;
            }
            
            return true;
        });
    }
    
    private function formatarDados($dados)
    {
        return array_map(function($item) {
            return [
                'id' => $item['id'],
                'numero_nfe' => $item['numero_nfe'],
                'chave_acesso' => $item['chave_acesso'],
                'pedido_id' => $item['pedido_id'],
                'numero_pedido' => $item['numero_pedido'] ?? '-',
                'fornecedor_id' => $item['fornecedor_id'],
                'fornecedor_nome' => $item['fornecedor_nome'] ?? 'N/A',
                'fornecedor_email' => $item['fornecedor_email'] ?? 'N/A',
                'data_emissao' => $this->formatarData($item['data_emissao']),
                'data_recebimento' => $this->formatarData($item['data_recebimento']),
                'valor_total' => $item['valor_total'],
                'status' => $item['status'],
                'observacoes' => $item['observacoes'] ?? '',
                'usuario_recebimento_id' => $item['usuario_recebimento_id'],
                'usuario_nome' => $item['usuario_nome'] ?? 'N/A',
                'created_at' => $this->formatarData($item['created_at']),
                'updated_at' => $this->formatarData($item['updated_at'])
            ];
        }, $dados);
    }
    
    private function formatarData($data)
    {
        if (empty($data)) return '-';
        
        try {
            $date = new \DateTime($data);
            return $date->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return $data;
        }
    }
}
