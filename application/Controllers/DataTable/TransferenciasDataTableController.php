<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class TransferenciasDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);

        $dataTable = new DataTableHelper('armazenagem_transferencias');

        $dataTable->addColumn('id', 'ID', 'number')
            ->addColumn('item_codigo', 'Item', 'text', [
                'select' => 'pnf.codigo_produto as item_codigo',
            ])
            ->addColumn('quantidade', 'Quantidade', 'number', [], 'text-center')
            ->addColumn('origem_codigo', 'Origem', 'text', [
                'select' => 'ao.codigo as origem_codigo',
            ])
            ->addColumn('destino_codigo', 'Destino', 'text', [
                'select' => 'ad.codigo as destino_codigo',
            ])
            ->addColumn('status', 'Status', 'status')
            ->addColumn('data_solicitacao', 'Solicitada em', 'datetime')
            ->addColumn('solicitante_nome', 'Solicitante', 'text', [
                'select' => 'us.nome as solicitante_nome',
            ])
            ->addColumn('actions', 'Ações', 'actions', [
                'actions' => [
                    [
                        'url' => DOMAIN . '/transferencias/view/:id',
                        'icon' => 'fas fa-eye',
                        'color' => 'outline-primary',
                        'label' => 'Detalhes',
                    ],
                ],
            ]);

        $dataTable->addSearchableColumn('armazenagem_transferencias.id')
            ->addSearchableColumn('pnf.codigo_produto')
            ->addSearchableColumn('us.nome');

        $dataTable->addOrderableColumn('id')
            ->addOrderableColumn('quantidade')
            ->addOrderableColumn('status')
            ->addOrderableColumn('data_solicitacao');

        $dataTable->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'pendente' => 'Pendente',
                'em_andamento' => 'Em Andamento',
                'concluida' => 'Concluída',
                'cancelada' => 'Cancelada',
            ],
        ]);

        $dataTable->addJoin('pedidos_nf pnf', 'armazenagem_transferencias.item_id = pnf.id', 'LEFT')
            ->addJoin('armazenagens ao', 'armazenagem_transferencias.armazenagem_origem = ao.id', 'LEFT')
            ->addJoin('armazenagens ad', 'armazenagem_transferencias.armazenagem_destino = ad.id', 'LEFT')
            ->addJoin('usuarios us', 'armazenagem_transferencias.usuario_solicitante = us.id', 'LEFT');

        $result = $dataTable->getData($_GET);
        $this->responseJson($result);
    }
}
