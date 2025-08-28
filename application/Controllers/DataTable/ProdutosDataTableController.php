<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;

class ProdutosDataTableController extends BaseDataTableController
{
    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('produtos');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                 ->addColumn('SKU', 'SKU', 'text')
                 ->addColumn('nome', 'Nome', 'text')
                 ->addColumn('estoque_atual', 'Estoque Atual', 'number')
                 ->addColumn('estoque_minimo', 'Estoque Mínimo', 'number')
                 ->addColumn('valor', 'Valor', 'text')
                 ->addColumn('status', 'Status', 'select', [
                     'options' => [
                         'ativo' => 'Ativo',
                         'inativo' => 'Inativo'
                     ]
                 ])
                 ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('SKU')
                 ->addSearchableColumn('nome');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('SKU')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('estoque_atual')
                 ->addOrderableColumn('status');

        // Configurar filtros
        $dataTable->addFilter('categoria', 'Categoria', 'select', [
            'options' => $this->getCategoriasOptions()
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo'
            ]
        ])
        ->addFilter('estoque', 'Estoque', 'select', [
            'options' => [
                '' => 'Todos',
                'baixo' => 'Estoque Baixo',
                'normal' => 'Estoque Normal'
            ]
        ]);

        // Adicionar condições baseadas nos filtros
        if (isset($_GET['filters']['estoque']) && $_GET['filters']['estoque'] === 'baixo') {
            $dataTable->addWhereCondition('estoque_atual <= estoque_minimo');
        } elseif (isset($_GET['filters']['estoque']) && $_GET['filters']['estoque'] === 'normal') {
            $dataTable->addWhereCondition('estoque_atual > estoque_minimo');
        }

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        // Formatar o campo valor para padrão brasileiro
        if (isset($result['data']) && is_array($result['data'])) {
            $this->formatarCamposValor($result['data'], ['valor']);
        }
        
        $this->responseJson($result);
    }

    /**
     * Obtém a lista de categorias para o filtro
     */
    private function getCategoriasOptions(): array
    {
        $read = new \Agencia\Close\Conn\Read();
        $read->FullRead("SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
        $categorias = $read->getResult() ?: [];
        
        $options = ['' => 'Todas'];
        foreach ($categorias as $cat) {
            $options[$cat['categoria']] = $cat['categoria'];
        }
        
        return $options;
    }
} 