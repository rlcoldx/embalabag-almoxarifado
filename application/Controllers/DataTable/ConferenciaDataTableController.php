<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Conferencia\ConferenciaRecebimento;
use Agencia\Close\Helpers\User\PermissionHelper;

class ConferenciaDataTableController extends Controller
{
    public function listar()
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $conferencia = new ConferenciaRecebimento();
        $result = $conferencia->getAllConferencias();

        if ($result->getResult()) {
            $data = [];
            foreach ($result->getResult() as $row) {
                // Preparar dados para DataTable
                $data[] = [
                    'id' => $row['id'],
                    'numero_nfe' => $row['numero_nfe'] ?? '-',
                    'fornecedor_nome' => $row['fornecedor_nome'] ?? '-',
                    'produto_nome' => $row['produto_nome'] ?? '-',
                    'variacao_info' => $this->formatarVariacao($row),
                    'quantidade_prevista' => $row['quantidade_prevista'] ?? 0,
                    'quantidade_conferida' => $row['quantidade_conferida'] ?? 0,
                    'status_qualidade' => $row['status_qualidade'] ?? 'pendente',
                    'status_integridade' => $row['status_integridade'] ?? 'integro',
                    'usuario_nome' => $row['usuario_nome'] ?? '-',
                    'data_conferencia' => $row['data_conferencia'] ?? null,
                    'status_conferencia' => $row['status_conferencia'] ?? 'pendente',
                    'actions' => $this->renderActions($row['id'])
                ];
            }

            // Retornar formato esperado pelo sistema de DataTable
            return $this->jsonResponse([
                'success' => true,
                'columns' => [
                    'id' => ['label' => 'ID', 'type' => 'number'],
                    'numero_nfe' => ['label' => 'Número NFE', 'type' => 'text'],
                    'fornecedor_nome' => ['label' => 'Fornecedor', 'type' => 'text'],
                    'produto_nome' => ['label' => 'Produto', 'type' => 'text'],
                    'variacao_info' => ['label' => 'Variação', 'type' => 'text'],
                    'quantidade_prevista' => ['label' => 'Qtd. Prevista', 'type' => 'number'],
                    'quantidade_conferida' => ['label' => 'Qtd. Conferida', 'type' => 'number'],
                    'status_qualidade' => ['label' => 'Qualidade', 'type' => 'select', 'options' => [
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                        'reprovado' => 'Reprovado'
                    ]],
                    'status_integridade' => ['label' => 'Integridade', 'type' => 'select', 'options' => [
                        'pendente' => 'Pendente',
                        'integro' => 'Íntegro',
                        'danificado' => 'Danificado'
                    ]],
                    'usuario_nome' => ['label' => 'Usuário', 'type' => 'text'],
                    'data_conferencia' => ['label' => 'Data Conferência', 'type' => 'datetime'],
                    'status_conferencia' => ['label' => 'Status', 'type' => 'select', 'options' => [
                        'pendente' => 'Pendente',
                        'em_andamento' => 'Em Andamento',
                        'concluida' => 'Concluída'
                    ]],
                    'actions' => ['label' => 'Ações', 'type' => 'actions']
                ],
                'filters' => [
                    'status_conferencia' => [
                        'label' => 'Status da Conferência',
                        'type' => 'select',
                        'options' => [
                            'pendente' => 'Pendente',
                            'em_andamento' => 'Em Andamento',
                            'concluida' => 'Concluída'
                        ]
                    ],
                    'status_qualidade' => [
                        'label' => 'Status da Qualidade',
                        'type' => 'select',
                        'options' => [
                            'pendente' => 'Pendente',
                            'aprovado' => 'Aprovado',
                            'reprovado' => 'Reprovado'
                        ]
                    ],
                    'fornecedor_nome' => [
                        'label' => 'Fornecedor',
                        'type' => 'text'
                    ]
                ],
                'orderable_columns' => ['id', 'numero_nfe', 'fornecedor_nome', 'produto_nome', 'data_conferencia', 'status_conferencia'],
                'data' => $data,
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 1,
                    'total_records' => count($data),
                    'records_per_page' => count($data),
                    'has_next_page' => false,
                    'has_prev_page' => false
                ]
            ]);
        }

        return $this->jsonResponse([
            'success' => true,
            'columns' => [],
            'filters' => [],
            'orderable_columns' => [],
            'data' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 1,
                'total_records' => 0,
                'records_per_page' => 0,
                'has_next_page' => false,
                'has_prev_page' => false
            ]
        ]);
    }

    private function formatarVariacao($row)
    {
        $variacao = [];
        
        if (!empty($row['tamanho'])) {
            $variacao[] = 'Tam: ' . $row['tamanho'];
        }
        
        if (!empty($row['cor_nome'])) {
            $variacao[] = 'Cor: ' . $row['cor_nome'];
        }
        
        return !empty($variacao) ? implode(' | ', $variacao) : '-';
    }

    private function renderActions($id)
    {
        return "
            <div class='btn-group' role='group'>
                <a href='/conferencia/{$id}/edit' class='btn btn-sm btn-outline-primary' title='Editar'>
                    <i class='fas fa-edit'></i>
                </a>
                <button type='button' class='btn btn-sm btn-outline-danger' title='Excluir' onclick='deleteConferencia({$id})'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
