<?php

namespace Agencia\Close\Controllers\Users;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\User\User;
use Agencia\Close\Models\User\Cargo;
use Agencia\Close\Helpers\User\PermissionHelper;

class UsersController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession(); // Verificar sessão antes de renderizar
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        // Renderizar apenas a página, os dados serão carregados via AJAX
        $this->render('pages/users/index.twig');
    }

    public function create(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'criar')) {
            echo 'Sem permissão para criar usuários.';
            return;
        }
        $cargo = new Cargo();
        $cargos = $cargo->getCargosAtivos();
        $this->render('pages/users/create.twig', [
            'cargos' => $cargos->getResult() ?? []
        ]);
    }

    public function store(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'criar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para criar usuários'
            ]);
            return;
        }
        $data = [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'tipo' => $_POST['tipo'] ?? '2',
            'status' => $_POST['status'] ?? 'ativo',
            'senha' => sha1($_POST['senha']),
            'sigla' => $_POST['sigla'] ?? null,
            'companhia' => $_POST['companhia'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'cnpj' => $_POST['cnpj'] ?? null,
            'cep' => $_POST['cep'] ?? null,
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'uf' => $_POST['uf'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (empty($data['nome']) || empty($data['email'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Nome e email são obrigatórios'
            ]);
            return;
        }
        $user = new User();
        $existingUser = $user->emailExist($data['email']);
        if ($existingUser->getResult()) {
            $this->responseJson([
                'success' => false,
                'error' => 'Email já cadastrado'
            ]);
            return;
        }
        $userId = $user->createUser($data);
        if ($userId) {
            if ($data['tipo'] == '2' && isset($_POST['cargos']) && is_array($_POST['cargos'])) {
                $user->setUserCargos($userId, $_POST['cargos']);
            }
            $this->responseJson([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'redirect' => DOMAIN . '/users'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao criar usuário'
            ]);
        }
    }

    public function edit(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'editar')) {
            echo 'Sem permissão para editar usuários.';
            return;
        }
        $userId = $params['id'] ?? null;
        if (!$userId) {
            echo 'ID do usuário não informado.';
            return;
        }
        $user = new User();
        $userData = $user->getUserWithCargos($userId);
        if (!$userData->getResult()) {
            echo 'Usuário não encontrado.';
            return;
        }
        $cargo = new Cargo();
        $cargos = $cargo->getCargosAtivos();
        $this->render('pages/users/edit.twig', [
            'user' => $userData->getResult()[0],
            'cargos' => $cargos->getResult() ?? []
        ]);
    }

    public function update(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'editar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para editar usuários'
            ]);
            return;
        }
        $userId = $params['id'] ?? null;
        if (!$userId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do usuário não informado'
            ]);
            return;
        }
        
        $data = [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'tipo' => $_POST['tipo'] ?? '2',
            'status' => $_POST['status'] ?? 'ativo',
            'sigla' => $_POST['sigla'] ?? null,
            'companhia' => $_POST['companhia'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'cnpj' => $_POST['cnpj'] ?? null,
            'cep' => $_POST['cep'] ?? null,
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'uf' => $_POST['uf'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($_POST['senha'])) {
            $data['senha'] = sha1($_POST['senha']);
        }
        
        if (empty($data['nome']) || empty($data['email'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Nome e email são obrigatórios'
            ]);
            return;
        }
        
        $user = new User();
        $existingUser = $user->emailExist($data['email']);
        
        if ($existingUser->getResult() && $existingUser->getResult()[0]['id'] != $userId) {
            $this->responseJson([
                'success' => false,
                'error' => 'Email já cadastrado'
            ]);
            return;
        }
        
        $updateResult = $user->updateUser($userId, $data);
        
        if ($updateResult) {
            if ($data['tipo'] == '2' && isset($_POST['cargos']) && is_array($_POST['cargos'])) {
                $cargosResult = $user->setUserCargos($userId, $_POST['cargos']);
                if (!$cargosResult) {
                    $this->responseJson([
                        'success' => false,
                        'error' => 'Usuário atualizado, mas erro ao atualizar cargos'
                    ]);
                    return;
                }
            } else {
                $cargosResult = $user->setUserCargos($userId, []);
                if (!$cargosResult) {
                    $this->responseJson([
                        'success' => false,
                        'error' => 'Usuário atualizado, mas erro ao limpar cargos'
                    ]);
                    return;
                }
            }
            
            $this->responseJson([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'redirect' => DOMAIN . '/users'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao atualizar usuário - Verifique se todos os campos estão corretos'
            ]);
        }
    }

    public function delete(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('usuarios', 'excluir')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para excluir usuários'
            ]);
            return;
        }
        $userId = $params['id'] ?? null;
        if (!$userId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do usuário não informado'
            ]);
            return;
        }
        if ($userId == $_SESSION[BASE . 'user_id']) {
            $this->responseJson([
                'success' => false,
                'error' => 'Não é possível excluir seu próprio usuário'
            ]);
            return;
        }
        $user = new User();
        if ($user->deleteUser($userId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao excluir usuário'
            ]);
        }
    }

    public function getFornecedores(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para acessar este módulo'
            ]);
            return;
        }
        
        $user = new User();
        $fornecedores = $user->getUsersByType('3'); // Tipo 3 = Fornecedor
        
        if ($fornecedores->getResult()) {
            $this->responseJson([
                'success' => true,
                'fornecedores' => $fornecedores->getResult()
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Nenhum fornecedor encontrado'
            ]);
        }
    }
}
