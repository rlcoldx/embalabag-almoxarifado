<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Helpers\DataTable\DataTableHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

class NotasFiscaisDataTableController extends BaseDataTableController
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
                 ->addColumn('valor_total', 'Valor Total', 'currency')
                 ->addColumn('status', 'Status', 'status')
                 ->addColumn('numero_pedido', 'Pedido', 'text', [
                     'select' => 'p.codigo as numero_pedido',
                 ])
                 ->addColumn('usuario_recebimento_nome', 'Recebido por', 'text', [
                     'select' => 'u1.nome as usuario_recebimento_nome',
                 ])
                 ->addColumn('usuario_conferencia_nome', 'Conferido por', 'text', [
                     'select' => 'u2.nome as usuario_conferencia_nome',
                 ])
                 ->addColumn('actions', 'Ações', 'actions', [
                     'actions' => [
                         [
                             'url' => DOMAIN . '/recebimento/notas-fiscais/show/:id',
                             'icon' => 'fas fa-eye',
                             'color' => 'outline-primary',
                             'label' => 'Detalhes',
                         ],
                         [
                             'url' => DOMAIN . '/recebimento/notas-fiscais/edit/:id',
                             'icon' => 'fas fa-edit',
                             'color' => 'outline-warning',
                             'label' => 'Editar',
                         ],
                     ],
                 ]);

        // Configurar colunas pesquisáveis
        $dataTable->addSearchableColumn('notas_fiscais.numero')
                 ->addSearchableColumn('notas_fiscais.fornecedor')
                 ->addSearchableColumn('p.codigo')
                 ->addSearchableColumn('u1.nome');

        // Configurar colunas ordenáveis
        $dataTable->addOrderableColumn('id')
                 ->addOrderableColumn('numero')
                 ->addOrderableColumn('fornecedor')
                 ->addOrderableColumn('data_emissao')
                 ->addOrderableColumn('valor_total')
                 ->addOrderableColumn('status');

        // Configurar filtros
        $dataTable->addFilter('status', 'Status', 'select', [
            'options' => [
                '' => 'Todos',
                'pendente' => 'Pendente',
                'recebida' => 'Recebida',
                'conferida' => 'Conferida',
                'finalizada' => 'Finalizada'
            ]
        ])
        ->addFilter('data_emissao', 'Data de Emissão', 'date_range')
        ->addFilter('usuario_recebimento', 'Responsável', 'select', [
            'options' => ResponsavelHelper::opcoesFiltro()
        ]);

        // Configurar joins
        $dataTable->addJoin('pedidos p', 'notas_fiscais.pedido_id = p.id', 'LEFT')
                 ->addJoin('usuarios u1', 'notas_fiscais.usuario_recebimento = u1.id', 'LEFT')
                 ->addJoin('usuarios u2', 'notas_fiscais.usuario_conferencia = u2.id', 'LEFT');

        // Buscar dados
        $result = $dataTable->getData($_GET);
        
        // Formatar o campo valor_total para padrão brasileiro
        if (isset($result['data']) && is_array($result['data'])) {
            $this->formatarCamposValor($result['data'], ['valor_total']);
        }
        
        $this->responseJson($result);
    }
} 