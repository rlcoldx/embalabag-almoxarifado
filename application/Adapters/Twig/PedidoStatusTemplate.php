<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PedidoStatusTemplate extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('pedidoStatusTemplate', [$this, 'pedidoStatusTemplate']),
        ];
    }

    public function pedidoStatusTemplate($status, $status_pedido, $codigo): string
    {
        $color = '';
        $icon = '';
        $texto = '';

        switch ($status_pedido) {
            //'Cancelado'
            case '0':
                $color = 'bg-danger';
                $icon = '<i class="fa-solid fa-face-frown-slight fa-4x"></i>';
                $texto = 'Infelizmente o pedido foi Cancelado.';
            break;
            //'Pendente'
            case '1':
                $color = 'bg-warning';
                $icon = '<i class="fa-solid fa-hourglass-clock fa-4x"></i>';
                $texto = 'O pedido está pendente.';
            break;
            //'Aprovado'
            case '2':
                $color = 'bg-info';
                $icon = '<i class="fa-solid fa-circle-check fa-4x"></i>';
                $texto = 'O pedido foi aprovado.';
            break;
            //'Em andamento'
            case '3':
                $color = 'bg-purple';
                $icon = '<i class="fa-regular fa-truck-fast fa-4x"></i>';
                $texto = 'O pedido está em Andamento.';
            break;
            //'Em Preparacao'
            case '4':
                $color = 'bg-purple';
                $icon = '<i class="fa-regular fa-truck-fast fa-4x"></i>';
                $texto = 'O pedido está em Preparação.';
            break;
            //'Aguardando Retorno Base'
            case '5':
                $color = 'bg-warning';
                $icon = '<i class="fa-solid fa-hourglass-clock fa-4x"></i>';
                $texto = 'O pedido está em Aguardando Retorno Base.';
            break;
            //'Enviado'
            case '6':
                $color = 'bg-success';
                $icon = '<i class="fa-duotone fa-box-check fa-4x"></i>';
                $texto = 'O pedido está foi enviado com sucesso.';
            break;
            //'Disponivel para Retirada'
            case '7':
                $color = 'bg-warning';
                $icon = '<i class="fa-duotone fa-box-check fa-4x"></i>';
                $texto = 'O pedido disponível para retirada.';
            break;
            //'Em Rota para Entrega'
            case '8':
                $color = 'bg-warning';
                $icon = '<i class="fa-regular fa-truck-fast fa-4x"></i>';
                $texto = 'O pedido em rota de entrega.';
            break;
            //'Concluido'
            case '9':
                $color = 'bg-success';
                $icon = '<i class="fa-duotone fa-box-check fa-4x"></i>';
                $texto = 'O pedido e entrega concluídos';
            break;
        }

        $return = '
                <div class="'.$color.' bg-opacity-25">
                    <div class="container text-center py-3">
                    <div class="text-primary">'.$icon.'</div>
                    <div class="mt-3">
                        <h2 class="text-black">Pedido: <span class="text-primary">'.$codigo.'</span></span></h2>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-black">'.$texto.'</h4>
                    </div>
                    </div>
                </div>
                ';


        return $return;

    }
}