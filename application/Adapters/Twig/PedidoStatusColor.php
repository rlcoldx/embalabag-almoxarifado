<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PedidoStatusColor extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('pedidoStatusColor', [$this, 'pedidoStatusColor']),
        ];
    }

    public function pedidoStatusColor($status_pedido): string
    {
        $color = '';

        switch ($status_pedido) {
            //'Cancelado'
            case '0':
                $color = 'bg-danger';
            break;
            //'Pendente'
            case '1':
                $color = 'bg-warning';
            break;
            //'Aprovado'
            case '2':
                $color = 'bg-info';
            break;
            //'Em andamento'
            case '3':
                $color = 'bg-purple';
            break;
            //'Em Preparacao'
            case '4':
                $color = 'bg-purple';
            break;
            //'Aguardando Retorno Base'
            case '5':
                $color = 'bg-warning';
            break;
            //'Enviado'
            case '6':
                $color = 'bg-success';
            break;
            //'Disponivel para Retirada'
            case '7':
                $color = 'bg-warning';
            break;
            //'Em Rota para Entrega'
            case '8':
                $color = 'bg-warning';
            break;
            //'Concluido'
            case '9':
                $color = 'bg-success';
            break;
        }
        return $color;
    }
}