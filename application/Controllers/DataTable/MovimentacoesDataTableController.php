<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class MovimentacoesDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);

        $dataTable = new DataTableHelper('movimentacoes_internas');

        $dataTable->addColumn('id', 'ID', 'number')
            ->addColumn('tipo_movimentacao', 'Tipo', 'select', [
                'options' => [
                    'put_away' => 'Put-away',
                    'transferencia' => 'Transferência',
                    'reposicao' => 'Reposição',
                    'ajuste' => 'Ajuste',
                ],
            ])
            ->addColumn('quantidade_movimentada', 'Quantidade', 'number', [], 'text-center')
            ->addColumn('status', 'Status', 'status')
            ->addColumn('data_movimentacao', 'Data', 'datetime')
            ->addColumn('usuario_nome', 'Usuário', 'text', [
                'select' => 'u.nome as usuario_nome',
            ])
            ->addColumn('origem_codigo', 'Origem', 'text', [
                'select' => 'a1.codigo as origem_codigo',
            ])
            ->addColumn('destino_codigo', 'Destino', 'text', [
                'select' => 'a2.codigo as destino_codigo',
            ])
            ->addColumn('actions', 'Ações', 'actions', [
                'actions' => [
                    [
                        'url' => DOMAIN . '/recebimento/movimentacoes/show/:id',
                        'icon' => 'fas fa-eye',
                        'color' => 'outline-primary',
                        'label' => 'Detalhes',
                    ],
                    [
                        'url' => DOMAIN . '/recebimento/movimentacoes/edit/:id',
                        'icon' => 'fas fa-edit',
                        'color' => 'outline-warning',
                        'label' => 'Editar',
                    ],
                ],
            ]);

        $dataTable->addSearchableColumn('movimentacoes_internas.id')
            ->addSearchableColumn('u.nome');

        $dataTable->addOrderableColumn('id')
            ->addOrderableColumn('tipo_movimentacao')
            ->addOrderableColumn('quantidade_movimentada')
            ->addOrderableColumn('status')
            ->addOrderableColumn('data_movimentacao');

        $dataTable->addFilter('tipo_movimentacao', 'Tipo', 'select', [
            'options' => [
                '' => 'Todos',
                'put_away' => 'Put-away',
                'transferencia' => 'Transferência',
                'reposicao' => 'Reposição',
                'ajuste' => 'Ajuste',
            ],
        ])
            ->addFilter('status', 'Status', 'select', [
                'options' => [
                    '' => 'Todos',
                    'pendente' => 'Pendente',
                    'em_andamento' => 'Em Andamento',
                    'concluida' => 'Concluída',
                    'cancelada' => 'Cancelada',
                ],
            ])
            ->addFilter('usuario_movimentacao', 'Responsável', 'select', [
                'options' => \Agencia\Close\Helpers\User\ResponsavelHelper::opcoesFiltro(),
            ]);

        $dataTable->addJoin('usuarios u', 'movimentacoes_internas.usuario_movimentacao = u.id', 'LEFT')
            ->addJoin('armazenagens a1', 'movimentacoes_internas.armazenagem_origem_id = a1.id', 'LEFT')
            ->addJoin('armazenagens a2', 'movimentacoes_internas.armazenagem_destino_id = a2.id', 'LEFT');

        $result = $dataTable->getData($_GET);
        $this->responseJson($result);
    }
}
