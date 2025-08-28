<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Movimentacao\MovimentacaoInterna;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Helpers\User\PermissionHelper;

class MovimentacoesController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $this->render('pages/recebimento/movimentacoes/index.twig', [
            'menu' => 'recebimento_movimentacoes'
        ]);
    }

    public function create(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'criar')) {
            echo 'Sem permissão para criar movimentações.';
            return;
        }
        
        $this->render('pages/recebimento/movimentacoes/create.twig', [
            'menu' => 'recebimento_movimentacoes'
        ]);
    }

    public function store(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para criar movimentações.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        
        $data = [
            'item_nf_id' => $_POST['item_nf_id'] ?? null,
            'tipo_movimentacao' => $_POST['tipo_movimentacao'] ?? '',
            'armazenagem_origem_id' => $_POST['armazenagem_origem_id'] ?? null,
            'armazenagem_destino_id' => $_POST['armazenagem_destino_id'] ?? null,
            'quantidade_movimentada' => $_POST['quantidade_movimentada'] ?? 0,
            'motivo' => $_POST['motivo'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_movimentacao_id' => $_SESSION['user_id'] ?? null,
            'data_movimentacao' => date('Y-m-d H:i:s'),
            'status' => 'pendente'
        ];
        
        if ($movimentacao->create($data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação criada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao criar movimentação.'
            ]);
        }
    }

    public function show(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'visualizar')) {
            echo 'Sem permissão para visualizar movimentações.';
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            echo 'ID da movimentação não informado.';
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        $dados = $movimentacao->getById($id);
        
        if (!$dados) {
            echo 'Movimentação não encontrada.';
            return;
        }
        
        $this->render('pages/recebimento/movimentacoes/show.twig', [
            'menu' => 'recebimento_movimentacoes',
            'movimentacao' => $dados
        ]);
    }

    public function edit(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'editar')) {
            echo 'Sem permissão para editar movimentações.';
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            echo 'ID da movimentação não informado.';
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        $dados = $movimentacao->getById($id);
        
        if (!$dados) {
            echo 'Movimentação não encontrada.';
            return;
        }
        
        $this->render('pages/recebimento/movimentacoes/edit.twig', [
            'menu' => 'recebimento_movimentacoes',
            'movimentacao' => $dados
        ]);
    }

    public function update(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'editar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para editar movimentações.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da movimentação não informado.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        
        $data = [
            'tipo_movimentacao' => $_POST['tipo_movimentacao'] ?? '',
            'armazenagem_origem_id' => $_POST['armazenagem_origem_id'] ?? null,
            'armazenagem_destino_id' => $_POST['armazenagem_destino_id'] ?? null,
            'quantidade_movimentada' => $_POST['quantidade_movimentada'] ?? 0,
            'motivo' => $_POST['motivo'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($movimentacao->update($id, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação atualizada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao atualizar movimentação.'
            ]);
        }
    }

    public function delete(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'excluir')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para excluir movimentações.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da movimentação não informado.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        
        if ($movimentacao->delete($id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação excluída com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao excluir movimentação.'
            ]);
        }
    }

    public function executar(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'executar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para executar movimentações.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da movimentação não informado.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        
        if ($movimentacao->executar($id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação executada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao executar movimentação.'
            ]);
        }
    }

    public function putAway(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'criar')) {
            echo 'Sem permissão para realizar put-away.';
            return;
        }
        
        $this->render('pages/recebimento/movimentacoes/put-away.twig');
    }

    public function realizarPutAway(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para realizar put-away.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        
        $data = [
            'item_nf_id' => $_POST['item_nf_id'] ?? null,
            'tipo_movimentacao' => 'put_away',
            'armazenagem_origem_id' => null,
            'armazenagem_destino_id' => $_POST['armazenagem_destino_id'] ?? null,
            'quantidade_movimentada' => $_POST['quantidade_movimentada'] ?? 0,
            'motivo' => 'Put-away automático',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_movimentacao_id' => $_SESSION['user_id'] ?? null,
            'data_movimentacao' => date('Y-m-d H:i:s'),
            'status' => 'concluida'
        ];
        
        if ($movimentacao->create($data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Put-away realizado com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao realizar put-away.'
            ]);
        }
    }

    public function transferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'criar')) {
            echo 'Sem permissão para realizar transferências.';
            return;
        }
        
        $this->render('pages/recebimento/movimentacoes/transferencia.twig');
    }

    public function realizarTransferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('movimentacao', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para realizar transferências.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        
        $data = [
            'item_nf_id' => $_POST['item_nf_id'] ?? null,
            'tipo_movimentacao' => 'transferencia',
            'armazenagem_origem_id' => $_POST['armazenagem_origem_id'] ?? null,
            'armazenagem_destino_id' => $_POST['armazenagem_destino_id'] ?? null,
            'quantidade_movimentada' => $_POST['quantidade_movimentada'] ?? 0,
            'motivo' => $_POST['motivo'] ?? 'Transferência entre armazenagens',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_movimentacao_id' => $_SESSION['user_id'] ?? null,
            'data_movimentacao' => date('Y-m-d H:i:s'),
            'status' => 'concluida'
        ];
        
        if ($movimentacao->create($data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Transferência realizada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao realizar transferência.'
            ]);
        }
    }
} 