<?php

namespace Agencia\Close\Controllers\DataTable;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\DataTable\DataTableHelper;

abstract class BaseDataTableController extends Controller
{
    protected function checkSessionAndSetParams(array $params): void
    {
        $this->checkSession();
        $this->setParams($params);
    }

    protected function responseJson($data): void
    {
        parent::responseJson($data);
    }

    /**
     * Formata um valor para o padrão brasileiro (R$ 1.050,00)
     */
    protected function formatarValorBrasileiro($valor): string
    {
        // Se o valor for nulo ou vazio, retornar "R$ 0,00"
        if (empty($valor) && $valor !== '0') {
            return 'R$ 0,00';
        }
        
        // Converter para float se for string
        $valor = (float) $valor;
        
        // Formatar para o padrão brasileiro
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Formata campos de valor em um array de dados
     */
    protected function formatarCamposValor(array &$data, array $campos): void
    {
        if (is_array($data)) {
            foreach ($data as &$row) {
                if (is_array($row)) {
                    foreach ($campos as $campo) {
                        if (isset($row[$campo])) {
                            $row[$campo] = $this->formatarValorBrasileiro($row[$campo]);
                        }
                    }
                }
            }
        }
    }
} 