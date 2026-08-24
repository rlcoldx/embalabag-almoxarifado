<?php

namespace Agencia\Close\Controllers\Home;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Services\Home\HomeStatsService;

class DashboardController extends Controller
{
    public function produtos(array $params)
    {
        $this->abrirDashboard($params, 'produtos', 'visualizar', 'dashboard_produtos', 'pages/dashboard/produtos.twig');
    }

    public function armazenagens(array $params)
    {
        $this->abrirDashboard($params, 'armazenagens', 'visualizar', 'dashboard_armazenagens', 'pages/dashboard/armazenagens.twig');
    }

    public function movimentacoes(array $params)
    {
        $this->abrirDashboard($params, 'movimentacoes', 'visualizar', 'dashboard_movimentacoes', 'pages/dashboard/movimentacoes.twig');
    }

    private function abrirDashboard(array $params, string $modulo, string $acao, string $menu, string $view): void
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission($modulo, $acao)) {
            echo 'Sem permissão para acessar este dashboard.';
            return;
        }

        $stats = (new HomeStatsService())->getDashboardStats();

        $this->render($view, [
            'menu' => $menu,
            'stats' => $stats
        ]);
    }
}
