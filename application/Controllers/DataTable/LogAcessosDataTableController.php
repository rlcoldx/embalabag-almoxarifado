<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class LogAcessosDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('log_acessos');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('usuario_nome', 'Usuário', 'text')
                 ->addColumn('email', 'Email', 'text')
                 ->addColumn('ip', 'IP', 'text')
                 ->addColumn('tipo_acesso', 'Tipo', 'select', [
                     'options' => [
                         'login' => 'Login',
                         'logout' => 'Logout',
                         'falha_login' => 'Falha no Login',
                         'timeout' => 'Timeout'
                     ]
                 ])
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'sucesso' => 'Sucesso',
                         'falha' => 'Falha'
                     ]
                 ])
                 ->addColumn('mensagem', 'Mensagem', 'text')
                 ->addColumn('data_acesso', 'Data/Hora', 'datetime');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('email')
                 ->addSearchableColumn('ip')
                 ->addSearchableColumn('mensagem');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('email')
                 ->addOrderableColumn('tipo_acesso')
                 ->addOrderableColumn('status')
                 ->addOrderableColumn('data_acesso');

        // Configurar filtros
        $dataTable->addFilter('tipo_acesso', 'Tipo de Acesso', 'select', [
            'options' => [
                '' => 'Todos',
                'login' => 'Login',
                'logout' => 'Logout',
                'falha_login' => 'Falha no Login',
                'timeout' => 'Timeout'
            ]
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'sucesso' => 'Sucesso',
                'falha' => 'Falha'
            ]
        ])
        ->addFilter('data_acesso', 'Data de Acesso', 'date_range');

        // Configurar joins
        $dataTable->addJoin('usuarios u', 'log_acessos.usuario_id = u.id', 'LEFT');

        // Configurar select personalizado para nome do usuário
        $dataTable->addColumn('usuario_nome', 'Usuário', 'text', [
            'select' => 'u.nome as usuario_nome'
        ]);

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        $this->responseJson($result);
    }

    public function export(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('log_acessos');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('usuario_nome', 'Usuário', 'text')
                 ->addColumn('email', 'Email', 'text')
                 ->addColumn('ip', 'IP', 'text')
                 ->addColumn('tipo_acesso', 'Tipo', 'select', [
                     'options' => [
                         'login' => 'Login',
                         'logout' => 'Logout',
                         'falha_login' => 'Falha no Login',
                         'timeout' => 'Timeout'
                     ]
                 ])
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'sucesso' => 'Sucesso',
                         'falha' => 'Falha'
                     ]
                 ])
                 ->addColumn('mensagem', 'Mensagem', 'text')
                 ->addColumn('data_acesso', 'Data/Hora', 'datetime');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('email')
                 ->addSearchableColumn('ip')
                 ->addSearchableColumn('mensagem');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('email')
                 ->addOrderableColumn('tipo_acesso')
                 ->addOrderableColumn('status')
                 ->addOrderableColumn('data_acesso');

        // Configurar filtros
        $dataTable->addFilter('tipo_acesso', 'Tipo de Acesso', 'select', [
            'options' => [
                '' => 'Todos',
                'login' => 'Login',
                'logout' => 'Logout',
                'falha_login' => 'Falha no Login',
                'timeout' => 'Timeout'
            ]
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'sucesso' => 'Sucesso',
                'falha' => 'Falha'
            ]
        ])
        ->addFilter('data_acesso', 'Data de Acesso', 'date_range');

        // Configurar joins
        $dataTable->addJoin('usuarios u', 'log_acessos.usuario_id = u.id', 'LEFT');

        // Configurar select personalizado para nome do usuário
        $dataTable->addColumn('usuario_nome', 'Usuário', 'text', [
            'select' => 'u.nome as usuario_nome'
        ]);

        // Buscar dados para exportação
        $result = $dataTable->getDataForExport($_GET);
        
        // Gerar CSV
        $csv = $dataTable->generateCSV($result['data'], $result['columns']);
        
        // Configurar headers para download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="log_acessos_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        echo $csv;
        exit;
    }
} 