<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PedidoStatusIcone extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('pedidoStatusIcone', [$this, 'pedidoStatusIcone']),
        ];
    }

    public function pedidoStatusIcone($status_pedido): string
    {
        $icon = '';

        switch ($status_pedido) {
            //'Cancelado'
            case '0':
                $icon = '<i class="fa-solid fa-face-frown-slight"></i>';
            break;
            //'Pendente'
            case '1':
                $icon = '<i class="fa-solid fa-hourglass-clock"></i>';
            break;
            //'Aprovado'
            case '2':
                $icon = '<i class="fa-solid fa-circle-check"></i>';
            break;
            //'Em andamento'
            case '3':
                $icon = '<i class="fa-solid fa-cart-flatbed-boxes"></i>';
            break;
            //'Em Preparacao'
            case '4':
                $icon = '<i class="fa-solid fa-box-open-full"></i>';
            break;
            //'Aguardando Retorno Base'
            case '5':
                $icon = '<i class="fa-solid fa-hourglass-clock"></i>';
            break;
            //'Enviado'
            case '6':
                $icon = '<i class="fa-duotone fa-box-check"></i>';
            break;
            //'Disponivel para Retirada'
            case '7':
                $icon = '<i class="fa-duotone fa-box-check"></i>';
            break;
            //'Em Rota para Entrega'
            case '8':
                $icon = '<i class="fa-regular fa-truck-fast"></i>';
            break;
            //'Concluido'
            case '9':
                $icon = '<i class="fa-duotone fa-box-check"></i>';
            break;
        }

        return $icon;

    }
}