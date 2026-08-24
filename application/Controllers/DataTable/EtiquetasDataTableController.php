<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class EtiquetasDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);

        $dataTable = new DataTableHelper('etiquetas_internas');

        $dataTable->addColumn('id', 'ID', 'number')
            ->addColumn('codigo', 'Código', 'text')
            ->addColumn('tipo_etiqueta', 'Tipo', 'select', [
                'options' => [
                    'localizacao' => 'Localização',
                    'palete' => 'Palete',
                    'caixa' => 'Caixa',
                    'produto' => 'Produto',
                    'armazenagem' => 'Armazenagem',
                ],
            ])
            ->addColumn('status', 'Status', 'status')
            ->addColumn('data_impressao', 'Impressão', 'datetime')
            ->addColumn('created_at', 'Criado em', 'datetime')
            ->addColumn('usuario_nome', 'Usuário', 'text', [
                'select' => 'u.nome as usuario_nome',
            ])
            ->addColumn('actions', 'Ações', 'actions', [
                'actions' => [
                    [
                        'url' => DOMAIN . '/recebimento/etiquetas/show/:id',
                        'icon' => 'fas fa-eye',
                        'color' => 'outline-primary',
                        'label' => 'Detalhes',
                    ],
                    [
                        'url' => DOMAIN . '/recebimento/etiquetas/edit/:id',
                        'icon' => 'fas fa-edit',
                        'color' => 'outline-warning',
                        'label' => 'Editar',
                    ],
                ],
            ]);

        $dataTable->addSearchableColumn('etiquetas_internas.codigo')
            ->addSearchableColumn('u.nome');

        $dataTable->addOrderableColumn('id')
            ->addOrderableColumn('codigo')
            ->addOrderableColumn('tipo_etiqueta')
            ->addOrderableColumn('status')
            ->addOrderableColumn('created_at');

        $dataTable->addFilter('tipo_etiqueta', 'Tipo', 'select', [
            'options' => [
                '' => 'Todos',
                'localizacao' => 'Localização',
                'palete' => 'Palete',
                'caixa' => 'Caixa',
                'produto' => 'Produto',
                'armazenagem' => 'Armazenagem',
            ],
        ])
            ->addFilter('status', 'Status', 'select', [
                'options' => [
                    '' => 'Todos',
                    'criada' => 'Criada',
                    'impressa' => 'Impressa',
                    'aplicada' => 'Aplicada',
                    'inativa' => 'Inativa',
                ],
            ]);

        $dataTable->addJoin('usuarios u', 'etiquetas_internas.usuario_criacao = u.id', 'LEFT');

        $result = $dataTable->getData($_GET);
        $this->responseJson($result);
    }
}
