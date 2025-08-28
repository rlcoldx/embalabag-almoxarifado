<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PedidoStatusName extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('pedidoStatusName', [$this, 'pedidoStatusName']),
        ];
    }

    public function pedidoStatusName($status_pedido): string
    {
        $texto = '';

        switch ($status_pedido) {
            //'Cancelado'
            case '0':
                $texto = 'Pedido Cancelado';
            break;
            //'Pendente'
            case '1':
                $texto = 'Pedido Pendente';
            break;
            //'Aprovado'
            case '2':
                $texto = 'Pedido Aprovado';
            break;
            //'Em andamento'
            case '3':
                $texto = 'Em Andamento';
            break;
            //'Em Preparacao'
            case '4':
                $texto = 'Em Preparação';
            break;
            //'Aguardando Retorno Base'
            case '5':
                $texto = 'Aguardando Retorno Base';
            break;
            //'Enviado'
            case '6':
                $texto = 'Enviado com Sucesso';
            break;
            //'Disponivel para Retirada'
            case '7':
                $texto = 'Disponível para Retirada';
            break;
            //'Em Rota para Entrega'
            case '8':
                $texto = 'Em rota de Entrega';
            break;
            //'Concluido'
            case '9':
                $texto = 'Concluído';
            break;
        }
        return $texto;
    }
}