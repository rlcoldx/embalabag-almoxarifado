<?php

namespace Agencia\Close\Controllers\Users;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\User\Cargo;
use Agencia\Close\Models\User\Permissao;
use Agencia\Close\Helpers\User\PermissionHelper;

class CargosController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession(); // Verificar sessão antes de renderizar
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('cargos', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        $cargo = new Cargo();
        $cargos = $cargo->getAllCargos();
        $this->render('pages/cargos/index.twig', [
            'cargos' => $cargos->getResult() ?? []
        ]);
    }

    public function create(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('cargos', 'criar')) {
            echo 'Sem permissão para criar cargos.';
            return;
        }
        $permissao = new Permissao();
        $permissoes = $permissao->getAllPermissoes();
        $this->render('pages/cargos/create.twig', [
            'permissoes' => $permissoes->getResult() ?? []
        ]);
    }

    public function store(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('cargos', 'criar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para criar cargos'
            ]);
            return;
        }
        $data = [
            'nome' => $_POST['nome'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'status' => $_POST['status'] ?? 'ativo'
        ];
        if (empty($data['nome'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Nome do cargo é obrigatório'
            ]);
            return;
        }
        $cargo = new Cargo();
        $cargoId = $cargo->createCargo($data);
        if ($cargoId) {
            // Debug: verificar se as permissões estão sendo recebidas
            error_log("Cargo criado com ID: " . $cargoId);
            error_log("Permissões recebidas: " . json_encode($_POST['permissoes'] ?? []));
            
            if (isset($_POST['permissoes']) && is_array($_POST['permissoes'])) {
                $result = $cargo->setPermissoesDoCargo($cargoId, $_POST['permissoes']);
                error_log("Resultado da definição de permissões: " . ($result ? 'true' : 'false'));
            }
            $this->responseJson([
                'success' => true,
                'message' => 'Cargo criado com sucesso',
                'redirect' => DOMAIN . '/cargos'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao criar cargo'
            ]);
        }
    }

    public function edit(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('cargos', 'editar')) {
            echo 'Sem permissão para editar cargos.';
            return;
        }
        $cargoId = $params['id'] ?? null;
        if (!$cargoId) {
            echo 'ID do cargo não informado.';
            return;
        }
        $cargo = new Cargo();
        $cargoData = $cargo->getCargoById($cargoId);
        if (!$cargoData->getResult()) {
            echo 'Cargo não encontrado.';
            return;
        }
        $permissoesCargo = $cargo->getPermissoesDoCargo($cargoId);
        $permissao = new Permissao();
        $todasPermissoes = $permissao->getAllPermissoes();
        $this->render('pages/cargos/edit.twig', [
            'cargo' => $cargoData->getResult()[0],
            'permissoes' => $todasPermissoes->getResult() ?? [],
            'permissoesCargo' => $permissoesCargo->getResult() ?? []
        ]);
    }

    public function update(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('cargos', 'editar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para editar cargos'
            ]);
            return;
        }
        $cargoId = $params['id'] ?? null;
        if (!$cargoId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do cargo não informado'
            ]);
            return;
        }
        $data = [
            'nome' => $_POST['nome'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'status' => $_POST['status'] ?? 'ativo'
        ];
        if (empty($data['nome'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Nome do cargo é obrigatório'
            ]);
            return;
        }
        $cargo = new Cargo();
        if ($cargo->updateCargo($cargoId, $data)) {
            // Debug: verificar se as permissões estão sendo recebidas
            error_log("Cargo atualizado com ID: " . $cargoId);
            error_log("Permissões recebidas: " . json_encode($_POST['permissoes'] ?? []));
            
            $permissoesIds = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];
            $result = $cargo->setPermissoesDoCargo($cargoId, $permissoesIds);
            error_log("Resultado da definição de permissões: " . ($result ? 'true' : 'false'));
            
            $this->responseJson([
                'success' => true,
                'message' => 'Cargo atualizado com sucesso',
                'redirect' => DOMAIN . '/cargos'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao atualizar cargo'
            ]);
        }
    }

    public function delete(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('cargos', 'excluir')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para excluir cargos'
            ]);
            return;
        }
        $cargoId = $params['id'] ?? null;
        if (!$cargoId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do cargo não informado'
            ]);
            return;
        }
        $cargo = new Cargo();
        if ($cargo->deleteCargo($cargoId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Cargo excluído com sucesso'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao excluir cargo. Verifique se não há usuários associados.'
            ]);
        }
    }
} 