<?php

namespace Agencia\Close\Controllers\Users;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\User\PermissionHelper;

class LogAcessosController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }

        $this->render('pages/log-acessos/index.twig', [
            'menu' => 'log_acessos'
        ]);
    }
}
