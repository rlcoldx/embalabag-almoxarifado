<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;
use Agencia\Close\Conn\Read;

class ProdutosDataTableController extends BaseDataTableController
{

    private string $tableWhere = '';

    public function index(array $params)
    {
        $this->checkSessionAndSetParams($params);
        
        $dataTable = new DataTableHelper('produtos');
        
        // Configurar colunas
        $dataTable->addColumn('id', 'ID', 'number')
                ->addColumn('SKU', 'SKU', 'text')
                ->addColumn('nome', 'Nome', 'text')
                ->addColumn('estoque_total', 'Estoque Total', 'number', [
                    'select' => '(SELECT COALESCE(SUM(estoque), 0) FROM produtos_variations WHERE produtos_variations.id_produto = produtos.id) as estoque_total'
                ], 'text-center')
                ->addColumn('valor', 'Valor', 'text')
                ->addColumn('status', 'Status', 'status')
                ->addColumn('actions', 'Ações', 'actions');

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('SKU')
                 ->addSearchableColumn('nome');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('SKU')
                 ->addOrderableColumn('nome')
                 ->addOrderableColumn('estoque_total')
                 ->addOrderableColumn('status');

        // Configurar filtros
        $dataTable->addFilter('categoria', 'Categoria', 'select', [
            'options' => $this->getCategoriasOptions()
        ])
        ->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'Publicado' => 'Publicado',
                'Rascunho' => 'Rascunho'
            ]
        ])
        ->addFilter('estoque', 'Estoque', 'select', [
            'skip_column' => true,
            'options' => [
                '' => 'Todos',
                'baixo' => 'Estoque Baixo',
                'normal' => 'Estoque Normal'
            ]
        ]);

        if (isset($_GET['filters']['estoque']) && $_GET['filters']['estoque'] === 'baixo') {
            $dataTable->addWhereCondition('EXISTS (
                SELECT 1 FROM produtos_variations pv
                WHERE pv.id_produto = produtos.id
                  AND pv.estoque <= IF(pv.estoque_minimo > 0, pv.estoque_minimo, 1)
            )');
        } elseif (isset($_GET['filters']['estoque']) && $_GET['filters']['estoque'] === 'normal') {
            $dataTable->addWhereCondition('NOT EXISTS (
                SELECT 1 FROM produtos_variations pv
                WHERE pv.id_produto = produtos.id
                  AND pv.estoque <= IF(pv.estoque_minimo > 0, pv.estoque_minimo, 1)
            )');
        }

        $this->tableWhere = '`status` <> "Deletado"';

        // Buscar dados
        $result = $dataTable->getData($_GET, $this->tableWhere);
        
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
        $read = new Read();
        $read->FullRead("SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
        $categorias = $read->getResult() ?: [];
        
        $options = ['' => 'Todas'];
        foreach ($categorias as $cat) {
            $options[$cat['categoria']] = $cat['categoria'];
        }
        
        return $options;
    }
} 