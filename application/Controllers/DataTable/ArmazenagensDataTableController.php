<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class ArmazenagensDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('armazenagens');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('codigo', 'Código', 'text')
                 ->addColumn('descricao', 'Descrição', 'text')
                 ->addColumn('tipo', 'Tipo', 'select', [
                     'options' => [
                         'prateleira' => 'Prateleira',
                         'gaveta' => 'Gaveta',
                         'caixa' => 'Caixa',
                         'pallet' => 'Pallet',
                         'area' => 'Área'
                     ]
                 ])
                 ->addColumn('setor', 'Setor', 'text')
                 ->addColumn('capacidade_atual', 'Capacidade Atual', 'number')
                 ->addColumn('capacidade_maxima', 'Capacidade Máxima', 'number')
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'ativo' => 'Ativo',
                         'inativo' => 'Inativo',
                         'bloqueado' => 'Bloqueado',
                         'manutencao' => 'Manutenção'
                     ]
                 ])
                 ->addColumn('actions', 'Ações', 'actions', [
                     'actions' => [
                         [
                             'url' => DOMAIN . '/armazenagens/:id',
                             'icon' => 'fas fa-eye',
                             'color' => 'outline-primary',
                             'label' => 'Detalhes'
                         ],
                         [
                             'url' => DOMAIN . '/armazenagens/:id/edit',
                             'icon' => 'fas fa-edit',
                             'color' => 'outline-warning',
                             'label' => 'Editar'
                         ]
                     ]
                 ]);

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('codigo')
                 ->addSearchableColumn('descricao')
                 ->addSearchableColumn('setor');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('codigo')
                 ->addOrderableColumn('descricao')
                 ->addOrderableColumn('tipo')
                 ->addOrderableColumn('setor')
                 ->addOrderableColumn('status');

        // Configurar filtros
        $dataTable->addFilter('tipo', 'Tipo', 'select', [
            'options' => [
                '' => 'Todos',
                'prateleira' => 'Prateleira',
                'gaveta' => 'Gaveta',
                'caixa' => 'Caixa',
                'pallet' => 'Pallet',
                'area' => 'Área'
            ]
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
                'bloqueado' => 'Bloqueado',
                'manutencao' => 'Manutenção'
            ]
        ])
        ->addFilter('setor', 'Setor', 'text');

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        $this->responseJson($result);
    }
} 