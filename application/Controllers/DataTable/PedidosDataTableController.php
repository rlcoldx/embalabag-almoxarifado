<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

class PedidosDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'visualizar')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        $dataTable = new DataTableHelper('pedidos');

        $dataTable->addColumn('id', 'ID', 'number')
            ->addColumn('codigo', 'Código', 'text')
            ->addColumn('nome_cliente', 'Fornecedor/Solicitante', 'text')
            ->addColumn('prioridade', 'Prioridade', 'select', [
                'options' => [
                    'Normal' => 'Normal',
                    'Urgente' => 'Urgente',
                ],
            ])
            ->addColumn('status_pedido', 'Status', 'select', [
                'options' => [
                    '0' => 'Cancelado',
                    '1' => 'Pendente',
                    '2' => 'Aprovado',
                    '3' => 'Em andamento',
                    '4' => 'Em Preparação',
                    '5' => 'Aguardando Retorno',
                    '6' => 'Enviado',
                    '7' => 'Disponível Retirada',
                    '8' => 'Em Rota',
                    '9' => 'Concluído',
                ],
            ])
            ->addColumn('valor_total', 'Valor Total', 'currency')
            ->addColumn('previsao_entrega', 'Previsão Entrega', 'text')
            ->addColumn('date_create', 'Criado em', 'datetime')
            ->addColumn('usuario_nome', 'Responsável', 'text', [
                'select' => 'u.nome as usuario_nome',
            ])
            ->addColumn('actions', 'Ações', 'actions', [
                'actions' => [
                    [
                        'url' => DOMAIN . '/pedidos/show/:id',
                        'icon' => 'fas fa-eye',
                        'color' => 'outline-primary',
                        'label' => 'Detalhes',
                    ],
                    [
                        'url' => DOMAIN . '/pedidos/edit/:id',
                        'icon' => 'fas fa-edit',
                        'color' => 'outline-warning',
                        'label' => 'Editar',
                    ],
                ],
            ]);

        $dataTable->addSearchableColumn('pedidos.codigo')
            ->addSearchableColumn('pedidos.nome_cliente')
            ->addSearchableColumn('u.nome');

        $dataTable->addOrderableColumn('id')
            ->addOrderableColumn('codigo')
            ->addOrderableColumn('nome_cliente')
            ->addOrderableColumn('prioridade')
            ->addOrderableColumn('status_pedido')
            ->addOrderableColumn('valor_total')
            ->addOrderableColumn('date_create');

        $dataTable->addFilter('status_pedido', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                '0' => 'Cancelado',
                '1' => 'Pendente',
                '2' => 'Aprovado',
                '3' => 'Em andamento',
                '4' => 'Em Preparação',
                '5' => 'Aguardando Retorno',
                '6' => 'Enviado',
                '7' => 'Disponível Retirada',
                '8' => 'Em Rota',
                '9' => 'Concluído',
            ],
        ])
            ->addFilter('prioridade', 'Prioridade', 'select', [
                'options' => [
                    '' => 'Todas',
                    'Normal' => 'Normal',
                    'Urgente' => 'Urgente',
                ],
            ])
            ->addFilter('id_user', 'Responsável', 'select', [
                'options' => ResponsavelHelper::opcoesFiltro(),
            ]);

        $dataTable->addJoin('usuarios u', 'pedidos.id_user = u.id', 'LEFT');

        $result = $dataTable->getData($_GET);

        $this->responseJson($result);
    }
}
