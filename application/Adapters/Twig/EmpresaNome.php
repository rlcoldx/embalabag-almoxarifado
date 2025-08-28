<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Agencia\Close\Helpers\String\Strings;

class EmpresaNome extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('empresaNome', [$this, 'empresaNome']),
        ];
    }

    public function empresaNome($nome): string
    {
        return Strings::abreviarNomeEmpresa($nome);
    }
} 