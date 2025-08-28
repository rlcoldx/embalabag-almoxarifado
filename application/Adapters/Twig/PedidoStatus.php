<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PedidoStatus extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('pedidoStatus', [$this, 'pedidoStatus']),
        ];
    }

    public function pedidoStatus($status): string
    {
        
        $cores = array(
            array("status" => "0", "color" => "bg-danger text-black"),
            array("status" => "1", "color" => "bg-warning text-black"),
            array("status" => "2", "color" => "bg-info text-black"),
            array("status" => "4", "color" => "bg-warning text-black"),
            array("status" => "5", "color" => "bg-warning text-black"),
            array("status" => "6", "color" => "bg-success text-black")
        );
        $return = '';

        foreach ($cores as $cor) {
            if ($cor["status"] === $status) {
                $return = $cor["color"];
                break;
            }
        }

        return $return;

    }
}