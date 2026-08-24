<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Recebimento\NotaFiscalEletronica;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Models\User\User;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

class NotaFiscalEletronicaController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }

        $user = new User();
        $fornecedores = $user->getUsersByType('3');

        $this->render('pages/recebimento/nfe/index.twig', [
            'menu' => 'recebimento_nfe',
            'fornecedores' => $fornecedores->getResult() ?? [],
            'usuarios_responsaveis' => ResponsavelHelper::listar(),
        ]);
    }

    public function create(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'criar')) {
            echo 'Sem permissão para criar NF-e.';
            return;
        }

        $user = new User();
        $fornecedores = $user->getUsersByType('3');

        $pedidos = new Pedidos();
        $pedidosPendentes = $pedidos->getPedidosByStatus('pendente');

        $this->render('pages/recebimento/nfe/create.twig', [
            'menu' => 'recebimento_nfe',
            'fornecedores' => $fornecedores->getResult() ?? [],
            'pedidos' => $pedidosPendentes->getResult() ?? [],
            'usuarios_responsaveis' => ResponsavelHelper::listar(),
        ]);
    }

    public function store(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'criar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para criar NF-e',
            ]);
            return;
        }

        $numeroNfe = trim($_POST['numero_nfe'] ?? '');
        $chaveAcesso = trim($_POST['chave_acesso'] ?? '');

        if ($numeroNfe === '' || $chaveAcesso === '') {
            $this->responseJson(['success' => false, 'error' => 'Número e chave de acesso são obrigatórios']);
            return;
        }

        $nfe = new NotaFiscalEletronica();

        if ($nfe->nfeExists($numeroNfe)) {
            $this->responseJson(['success' => false, 'error' => 'NF-e já cadastrada']);
            return;
        }

        if ($nfe->chaveExists($chaveAcesso)) {
            $this->responseJson(['success' => false, 'error' => 'Chave de acesso já cadastrada']);
            return;
        }

        $data = [
            'numero_nfe' => $numeroNfe,
            'chave_acesso' => $chaveAcesso,
            'pedido_id' => !empty($_POST['pedido_id']) ? (int) $_POST['pedido_id'] : null,
            'fornecedor_id' => (int) ($_POST['fornecedor_id'] ?? 0),
            'data_emissao' => $_POST['data_emissao'],
            'data_recebimento' => date('Y-m-d H:i:s'),
            'valor_total' => $_POST['valor_total'],
            'status' => 'pendente',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_recebimento_id' => ResponsavelHelper::idFromPost('usuario_recebimento_id', (int) ($_SESSION[BASE.'user_id'] ?? 0)),
        ];

        $nfeId = $nfe->createNFe($data);

        if ($nfeId) {
            if (isset($_POST['itens']) && is_array($_POST['itens'])) {
                foreach ($_POST['itens'] as $item) {
                    $quantidade = (int) ($item['quantidade'] ?? 0);
                    $valorUnitario = (float) ($item['valor_unitario'] ?? 0);
                    if ($quantidade <= 0) {
                        continue;
                    }

                    $nfe->addItem([
                        'nfe_id' => $nfeId,
                        'produto_id' => (int) $item['produto_id'],
                        'variacao_id' => (int) $item['variacao_id'],
                        'quantidade' => $quantidade,
                        'valor_unitario' => $valorUnitario,
                        'valor_total' => $quantidade * $valorUnitario,
                    ]);
                }
            }

            $this->responseJson([
                'success' => true,
                'message' => 'NF-e criada com sucesso',
                'redirect' => DOMAIN . '/recebimento/nfe/show/' . $nfeId,
            ]);
        } else {
            $this->responseJson(['success' => false, 'error' => 'Erro ao criar NF-e']);
        }
    }

    public function show(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            echo 'Sem permissão para visualizar NF-e.';
            return;
        }

        $nfeId = (int) ($params['id'] ?? 0);
        if ($nfeId <= 0) {
            echo 'ID da NF-e não informado.';
            return;
        }

        $nfe = new NotaFiscalEletronica();
        $result = $nfe->getById($nfeId);
        if (!$result->getResult()) {
            echo 'NF-e não encontrada.';
            return;
        }

        $itens = $nfe->getItens($nfeId);

        $this->render('pages/recebimento/nfe/show.twig', [
            'menu' => 'recebimento_nfe',
            'nfe' => $result->getResult()[0],
            'itens' => $itens->getResult() ?? [],
        ]);
    }

    public function edit(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'editar')) {
            echo 'Sem permissão para editar NF-e.';
            return;
        }

        $nfeId = (int) ($params['id'] ?? 0);
        if ($nfeId <= 0) {
            echo 'ID da NF-e não informado.';
            return;
        }

        $nfe = new NotaFiscalEletronica();
        $result = $nfe->getById($nfeId);
        if (!$result->getResult()) {
            echo 'NF-e não encontrada.';
            return;
        }

        $itens = $nfe->getItens($nfeId);

        $user = new User();
        $fornecedores = $user->getUsersByType('3');

        $pedidos = new Pedidos();
        $pedidosPendentes = $pedidos->getPedidosByStatus('pendente');

        $this->render('pages/recebimento/nfe/edit.twig', [
            'menu' => 'recebimento_nfe',
            'nfe' => $result->getResult()[0],
            'itens' => $itens->getResult() ?? [],
            'fornecedores' => $fornecedores->getResult() ?? [],
            'pedidos' => $pedidosPendentes->getResult() ?? [],
            'usuarios_responsaveis' => ResponsavelHelper::listar(),
        ]);
    }

    public function update(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'editar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para editar NF-e',
            ]);
            return;
        }

        $nfeId = (int) ($params['id'] ?? 0);
        if ($nfeId <= 0) {
            $this->responseJson(['success' => false, 'error' => 'ID da NF-e não informado']);
            return;
        }

        $nfe = new NotaFiscalEletronica();

        $data = [
            'numero_nfe' => trim($_POST['numero_nfe'] ?? ''),
            'chave_acesso' => trim($_POST['chave_acesso'] ?? ''),
            'pedido_id' => !empty($_POST['pedido_id']) ? (int) $_POST['pedido_id'] : null,
            'fornecedor_id' => (int) ($_POST['fornecedor_id'] ?? 0),
            'data_emissao' => $_POST['data_emissao'] ?? null,
            'valor_total' => $_POST['valor_total'] ?? 0,
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_recebimento_id' => ResponsavelHelper::idFromPost('usuario_recebimento_id', (int) ($_SESSION[BASE.'user_id'] ?? 0)),
        ];

        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $data['status'] = $_POST['status'];
        }

        if ($nfe->updateNFe($nfeId, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'NF-e atualizada com sucesso',
                'redirect' => DOMAIN . '/recebimento/nfe/show/' . $nfeId,
            ]);
        } else {
            $this->responseJson(['success' => false, 'error' => 'Erro ao atualizar NF-e']);
        }
    }

    public function delete(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'excluir')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para excluir NF-e',
            ]);
            return;
        }

        $nfeId = (int) ($params['id'] ?? 0);
        if ($nfeId <= 0) {
            $this->responseJson(['success' => false, 'error' => 'ID da NF-e não informado']);
            return;
        }

        $nfe = new NotaFiscalEletronica();

        if ($nfe->deleteNFe($nfeId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'NF-e excluída com sucesso',
            ]);
        } else {
            $this->responseJson(['success' => false, 'error' => 'Erro ao excluir NF-e']);
        }
    }

    public function buscarPedido(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $numeroPedido = trim($_GET['numero'] ?? '');
        if ($numeroPedido === '') {
            $this->responseJson([
                'success' => false,
                'error' => 'Número do pedido não informado',
            ]);
            return;
        }

        $pedidos = new Pedidos();
        $pedido = $pedidos->getPedidoByNumero($numeroPedido);

        if ($pedido->getResult()) {
            $this->responseJson([
                'success' => true,
                'pedido' => $pedido->getResult()[0],
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Pedido não encontrado',
            ]);
        }
    }
}
