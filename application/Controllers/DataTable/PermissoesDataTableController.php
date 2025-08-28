<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class PermissoesDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('permissoes');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('descricao', 'Descrição', 'text')
                 ->addColumn('modulo', 'Módulo', 'text')
                 ->addColumn('acao', 'Ação', 'text')
                 ->addColumn('created_at', 'Criado em', 'datetime')
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('nome')
                 ->addSearchableColumn('descricao')
                 ->addSearchableColumn('modulo')
                 ->addSearchableColumn('acao');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('modulo')
                 ->addOrderableColumn('acao')
                 ->addOrderableColumn('created_at');

        // Configurar filtros
        $dataTable->addFilter('modulo', 'Módulo', 'select', [
            'options' => [
                '' => 'Todos',
                'usuarios' => 'Usuários',
                'produtos' => 'Produtos',
                'estoque' => 'Estoque',
                'relatorios' => 'Relatórios',
                'configuracoes' => 'Configurações'
            ]
        ])
        ->addFilter('acao', 'Ação', 'select', [
            'options' => [
                '' => 'Todas',
                'visualizar' => 'Visualizar',
                'criar' => 'Criar',
                'editar' => 'Editar',
                'excluir' => 'Excluir',
                'movimentar' => 'Movimentar',
                'gerar' => 'Gerar',
                'acessar' => 'Acessar'
            ]
        ]);

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        $this->responseJson($result);
    }

    public function export(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('permissoes');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('descricao', 'Descrição', 'text')
                 ->addColumn('modulo', 'Módulo', 'text')
                 ->addColumn('acao', 'Ação', 'text')
                 ->addColumn('created_at', 'Criado em', 'datetime')
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('nome')
                 ->addSearchableColumn('descricao')
                 ->addSearchableColumn('modulo')
                 ->addSearchableColumn('acao');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('modulo')
                 ->addOrderableColumn('acao')
                 ->addOrderableColumn('created_at');

        // Configurar filtros
        $dataTable->addFilter('modulo', 'Módulo', 'select', [
            'options' => [
                '' => 'Todos',
                'usuarios' => 'Usuários',
                'produtos' => 'Produtos',
                'estoque' => 'Estoque',
                'relatorios' => 'Relatórios',
                'configuracoes' => 'Configurações'
            ]
        ])
        ->addFilter('acao', 'Ação', 'select', [
            'options' => [
                '' => 'Todas',
                'visualizar' => 'Visualizar',
                'criar' => 'Criar',
                'editar' => 'Editar',
                'excluir' => 'Excluir',
                'movimentar' => 'Movimentar',
                'gerar' => 'Gerar',
                'acessar' => 'Acessar'
            ]
        ]);

        // Buscar dados para exportação
        $result = $dataTable->getDataForExport($_GET);
        
        // Gerar CSV
        $csv = $dataTable->generateCSV($result['data'], $result['columns']);
        
        // Configurar headers para download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="permissoes_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        echo $csv;
        exit;
    }
} 