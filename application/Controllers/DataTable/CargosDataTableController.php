<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class CargosDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('cargos');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('descricao', 'Descrição', 'text')
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'ativo' => 'Ativo',
                         'inativo' => 'Inativo'
                     ]
                 ])
                 ->addColumn('permissoes', 'Permissões', 'text')
                 ->addColumn('created_at', 'Criado em', 'datetime')
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('nome')
                 ->addSearchableColumn('descricao');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('status')
                 ->addOrderableColumn('created_at');

        // Configurar filtros
        $dataTable->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo'
            ]
        ])
        ->addFilter('created_at', 'Data de Criação', 'date_range');

        // Configurar joins
        $dataTable->addJoin('cargo_permissoes cp', 'cargos.id = cp.cargo_id', 'LEFT')
                 ->addJoin('permissoes p', 'cp.permissao_id = p.id', 'LEFT');

        // Configurar select personalizado para permissões
        $dataTable->addColumn('permissoes', 'Permissões', 'text', [
            'select' => 'GROUP_CONCAT(p.nome SEPARATOR ", ") as permissoes'
        ]);

        // Configurar group by
        $dataTable->addGroupBy('cargos.id');

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        $this->responseJson($result);
    }

    public function export(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('cargos');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('descricao', 'Descrição', 'text')
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'ativo' => 'Ativo',
                         'inativo' => 'Inativo'
                     ]
                 ])
                 ->addColumn('permissoes', 'Permissões', 'text')
                 ->addColumn('created_at', 'Criado em', 'datetime')
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('nome')
                 ->addSearchableColumn('descricao');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('status')
                 ->addOrderableColumn('created_at');

        // Configurar filtros
        $dataTable->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo'
            ]
        ])
        ->addFilter('created_at', 'Data de Criação', 'date_range');

        // Configurar joins
        $dataTable->addJoin('cargo_permissoes cp', 'cargos.id = cp.cargo_id', 'LEFT')
                 ->addJoin('permissoes p', 'cp.permissao_id = p.id', 'LEFT');

        // Configurar select personalizado para permissões
        $dataTable->addColumn('permissoes', 'Permissões', 'text', [
            'select' => 'GROUP_CONCAT(p.nome SEPARATOR ", ") as permissoes'
        ]);

        // Configurar group by
        $dataTable->addGroupBy('cargos.id');

        // Buscar dados para exportação
        $result = $dataTable->getDataForExport($_GET);
        
        // Gerar CSV
        $csv = $dataTable->generateCSV($result['data'], $result['columns']);
        
        // Configurar headers para download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="cargos_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        echo $csv;
        exit;
    }
} 