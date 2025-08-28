<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\TwigFilter;
use Agencia\Close\Models\Produtos\Cor;
use Twig\Extension\AbstractExtension;

class ColorVariation extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('colorVariation', [$this, 'colorVariation']),
        ];
    }

    public function colorVariation($status): string
    {
        $cores = new Cor();
        $cores = $cores->getCorPorNome($status);
        $return = '';
        if($cores->getResult()){
            $return = $cores->getResult()[0]['cor'];
        }
        return $return;
    }
}