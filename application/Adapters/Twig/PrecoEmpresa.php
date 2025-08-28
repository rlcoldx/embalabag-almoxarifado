<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Agencia\Close\Services\PrecoEmpresa\PrecoEmpresaService;

class PrecoEmpresa extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('precoEmpresa', [$this, 'precoEmpresa']),
        ];
    }

    public function precoEmpresa($produto, $session): float
    {
        $precoService = new PrecoEmpresaService();
        $precoEmpresa = $precoService->getPrecoPorTipoUsuario($produto['id'], $session);
        
        // Se encontrou preço específico da empresa, retorna ele
        if ($precoEmpresa > 0) {
            return $precoEmpresa;
        }
        
        // Se não encontrou, retorna o preço padrão do produto
        return floatval($produto['valor'] ?? 0);
    }
} 