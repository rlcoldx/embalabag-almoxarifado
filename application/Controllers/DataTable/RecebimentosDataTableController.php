<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class RecebimentosDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('notas_fiscais');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('numero', 'Número', 'text')
                 ->addColumn('serie', 'Série', 'text')
                 ->addColumn('fornecedor', 'Fornecedor', 'text')
                 ->addColumn('data_emissao', 'Data Emissão', 'date')
                 ->addColumn('data_recebimento', 'Data Recebimento', 'date')
                 ->addColumn('valor_total', 'Valor Total', 'currency')
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'pendente' => 'Pendente',
                         'recebida' => 'Recebida',
                         'conferida' => 'Conferida',
                         'finalizada' => 'Finalizada',
                         'cancelada' => 'Cancelada'
                     ]
                 ])
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('numero')
                 ->addSearchableColumn('fornecedor')
                 ->addSearchableColumn('serie');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('numero')
                 ->addOrderableColumn('fornecedor')
                 ->addOrderableColumn('data_emissao')
                 ->addOrderableColumn('data_recebimento')
                 ->addOrderableColumn('valor_total')
                 ->addOrderableColumn('status');

        // Configurar filtros
        $dataTable->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'pendente' => 'Pendente',
                'recebida' => 'Recebida',
                'conferida' => 'Conferida',
                'finalizada' => 'Finalizada',
                'cancelada' => 'Cancelada'
            ]
        ])
        ->addFilter('data_emissao', 'Data de Emissão', 'date_range')
        ->addFilter('data_recebimento', 'Data de Recebimento', 'date_range');

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        // Formatar o campo valor_total para padrão brasileiro
        if (isset($result['data']) && is_array($result['data'])) {
            $this->formatarCamposValor($result['data'], ['valor_total']);
        }
        
        $this->responseJson($result);
    }
} 