<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Helpers\User\PermissionHelper;

class ArmazenagensApiController extends Controller
{
    public function listar()
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Sem permissão']);
        }

        $armazenagem = new Armazenagem();
        $result = $armazenagem->getAllArmazenagens();

        if ($result->getResult()) {
            return $this->jsonResponse([
                'success' => true,
                'armazenagens' => $result->getResult()
            ]);
        }

        return $this->jsonResponse(['success' => false, 'message' => 'Nenhuma armazenagem encontrada']);
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
