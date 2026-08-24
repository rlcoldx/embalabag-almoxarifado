<?php

namespace Agencia\Close\Controllers\Users;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\User\PermissionHelper;

class PermissoesController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (
            !$permissionHelper->userHasPermission('usuarios', 'visualizar')
            && !$permissionHelper->userHasPermission('cargos', 'visualizar')
        ) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }

        $this->render('pages/permissoes/index.twig', [
            'menu' => 'permissoes'
        ]);
    }
}
