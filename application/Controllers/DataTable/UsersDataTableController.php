<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class UsersDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('usuarios');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('email', 'Email', 'text')
                 ->addColumn('tipo', 'Tipo', 'select', [
                     'options' => [
                         '1' => 'Administrador',
                         '2' => 'Funcionário',
                         '3' => 'Companhia',
                         '4' => 'Inativo'
                     ]
                 ])
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'ativo' => 'Ativo',
                         'inativo' => 'Inativo',
                         'bloqueado' => 'Bloqueado'
                     ]
                 ])
                 ->addColumn('cargos', 'Cargos', 'text')
                 ->addColumn('ultimo_acesso', 'Último Acesso', 'datetime')
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('nome')
                 ->addSearchableColumn('email')
                 ->addSearchableColumn('sigla')
                 ->addSearchableColumn('companhia');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('email')
                 ->addOrderableColumn('tipo')
                 ->addOrderableColumn('status')
                 ->addOrderableColumn('ultimo_acesso');

        // Configurar filtros
        $dataTable->addFilter('tipo', 'Tipo de Usuário', 'select', [
            'options' => [
                '' => 'Todos',
                '1' => 'Administrador',
                '2' => 'Funcionário',
                '3' => 'Companhia',
                '4' => 'Inativo'
            ]
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
                'bloqueado' => 'Bloqueado'
            ]
        ])
        ->addFilter('created_at', 'Data de Criação', 'date_range');

        // Configurar joins
        $dataTable->addJoin('usuario_cargos uc', 'usuarios.id = uc.usuario_id', 'LEFT')
                 ->addJoin('cargos c', 'uc.cargo_id = c.id', 'LEFT');

        // Configurar select personalizado para cargos
        $dataTable->addColumn('cargos', 'Cargos', 'text', [
            'select' => 'GROUP_CONCAT(c.nome SEPARATOR ", ") as cargos'
        ]);

        // Configurar group by
        $dataTable->addGroupBy('usuarios.id');

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        $this->responseJson($result);
    }

    public function export(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('usuarios');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('email', 'Email', 'text')
                 ->addColumn('tipo', 'Tipo', 'select', [
                     'options' => [
                         '1' => 'Administrador',
                         '2' => 'Funcionário',
                         '3' => 'Companhia',
                         '4' => 'Inativo'
                     ]
                 ])
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'ativo' => 'Ativo',
                         'inativo' => 'Inativo',
                         'bloqueado' => 'Bloqueado'
                     ]
                 ])
                 ->addColumn('cargos', 'Cargos', 'text')
                 ->addColumn('ultimo_acesso', 'Último Acesso', 'datetime')
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('nome')
                 ->addSearchableColumn('email')
                 ->addSearchableColumn('sigla')
                 ->addSearchableColumn('companhia');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('email')
                 ->addOrderableColumn('tipo')
                 ->addOrderableColumn('status')
                 ->addOrderableColumn('ultimo_acesso');

        // Configurar filtros
        $dataTable->addFilter('tipo', 'Tipo de Usuário', 'select', [
            'options' => [
                '' => 'Todos',
                '1' => 'Administrador',
                '2' => 'Funcionário',
                '3' => 'Companhia',
                '4' => 'Inativo'
            ]
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
                'bloqueado' => 'Bloqueado'
            ]
        ])
        ->addFilter('created_at', 'Data de Criação', 'date_range');

        // Configurar joins
        $dataTable->addJoin('usuario_cargos uc', 'usuarios.id = uc.usuario_id', 'LEFT')
                 ->addJoin('cargos c', 'uc.cargo_id = c.id', 'LEFT');

        // Configurar select personalizado para cargos
        $dataTable->addColumn('cargos', 'Cargos', 'text', [
            'select' => 'GROUP_CONCAT(c.nome SEPARATOR ", ") as cargos'
        ]);

        // Configurar group by
        $dataTable->addGroupBy('usuarios.id');

        // Buscar dados para exportação
        $result = $dataTable->getDataForExport($_GET);
        
        // Gerar CSV
        $csv = $dataTable->generateCSV($result['data'], $result['columns']);
        
        // Configurar headers para download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        echo $csv;
        exit;
    }
} 