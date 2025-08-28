<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Recebimento\NotaFiscalEletronica;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Models\User\User;
use Agencia\Close\Helpers\User\PermissionHelper;

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
        
        $this->render('pages/recebimento/nfe/index.twig', ['menu' => 'recebimento']);
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
        
        // Buscar fornecedores (usuários tipo fornecedor)
        $user = new User();
        $fornecedores = $user->getUsersByType('3'); // Tipo 3 = Fornecedor
        
        // Buscar pedidos pendentes
        $pedidos = new Pedidos();
        $pedidosPendentes = $pedidos->getPedidosByStatus('pendente');
        
        $this->render('pages/recebimento/nfe/create.twig', [
            'menu' => 'recebimento',
            'fornecedores' => $fornecedores->getResult() ?? [],
            'pedidos' => $pedidosPendentes->getResult() ?? []
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
                'error' => 'Sem permissão para criar NF-e'
            ]);
            return;
        }
        
        $nfe = new NotaFiscalEletronica();
        
        // Verificar se NF-e já existe
        if ($nfe->nfeExists($_POST['numero_nfe'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'NF-e já cadastrada'
            ]);
            return;
        }
        
        // Verificar se chave de acesso já existe
        if ($nfe->chaveExists($_POST['chave_acesso'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Chave de acesso já cadastrada'
            ]);
            return;
        }
        
        $data = [
            'numero_nfe' => $_POST['numero_nfe'],
            'chave_acesso' => $_POST['chave_acesso'],
            'pedido_id' => !empty($_POST['pedido_id']) ? $_POST['pedido_id'] : null,
            'fornecedor_id' => $_POST['fornecedor_id'],
            'data_emissao' => $_POST['data_emissao'],
            'data_recebimento' => date('Y-m-d H:i:s'),
            'valor_total' => $_POST['valor_total'],
            'status' => 'pendente',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_recebimento_id' => $_SESSION[BASE.'user_id']
        ];
        
        $nfeId = $nfe->createNFe($data);
        
        if ($nfeId) {
            // Adicionar itens da NF-e
            if (isset($_POST['itens']) && is_array($_POST['itens'])) {
                foreach ($_POST['itens'] as $item) {
                    $itemData = [
                        'nfe_id' => $nfeId,
                        'produto_id' => $item['produto_id'],
                        'variacao_id' => $item['variacao_id'],
                        'quantidade' => $item['quantidade'],
                        'valor_unitario' => $item['valor_unitario'],
                        'valor_total' => $item['quantidade'] * $item['valor_unitario']
                    ];
                    $nfe->addItem($itemData);
                }
            }
            
            $this->responseJson([
                'success' => true,
                'message' => 'NF-e criada com sucesso',
                'redirect' => DOMAIN . '/recebimento/nfe'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao criar NF-e'
            ]);
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
        
        $nfeId = $params['id'] ?? null;
        if (!$nfeId) {
            echo 'ID da NF-e não informado.';
            return;
        }
        
        $nfe = new NotaFiscalEletronica();
        $nfeData = $nfe->getAllWithDetails();
        $nfeData = $nfeData->getResult();
        
        if (!$nfeData) {
            echo 'NF-e não encontrada.';
            return;
        }
        
        // Buscar NF-e específica
        $nfeEspecifica = null;
        foreach ($nfeData as $n) {
            if ($n['id'] == $nfeId) {
                $nfeEspecifica = $n;
                break;
            }
        }
        
        if (!$nfeEspecifica) {
            echo 'NF-e não encontrada.';
            return;
        }
        
        // Buscar itens da NF-e
        $itens = $nfe->getItens($nfeId);
        
        $this->render('pages/recebimento/nfe/show.twig', [
            'menu' => 'recebimento',
            'nfe' => $nfeEspecifica,
            'itens' => $itens->getResult() ?? []
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
        
        $nfeId = $params['id'] ?? null;
        if (!$nfeId) {
            echo 'ID da NF-e não informado.';
            return;
        }
        
        $nfe = new NotaFiscalEletronica();
        $nfeData = $nfe->getAllWithDetails();
        $nfeData = $nfeData->getResult();
        
        if (!$nfeData) {
            echo 'NF-e não encontrada.';
            return;
        }
        
        // Buscar NF-e específica
        $nfeEspecifica = null;
        foreach ($nfeData as $n) {
            if ($n['id'] == $nfeId) {
                $nfeEspecifica = $n;
                break;
            }
        }
        
        if (!$nfeEspecifica) {
            echo 'NF-e não encontrada.';
            return;
        }
        
        // Buscar itens da NF-e
        $itens = $nfe->getItens($nfeId);
        
        // Buscar fornecedores
        $user = new User();
        $fornecedores = $user->getUsersByType('3');
        
        // Buscar pedidos
        $pedidos = new Pedidos();
        $pedidosPendentes = $pedidos->getPedidosByStatus('pendente');
        
        $this->render('pages/recebimento/nfe/edit.twig', [
            'menu' => 'recebimento',
            'nfe' => $nfeEspecifica,
            'itens' => $itens->getResult() ?? [],
            'fornecedores' => $fornecedores->getResult() ?? [],
            'pedidos' => $pedidosPendentes->getResult() ?? []
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
                'error' => 'Sem permissão para editar NF-e'
            ]);
            return;
        }
        
        $nfeId = $params['id'] ?? null;
        if (!$nfeId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da NF-e não informado'
            ]);
            return;
        }
        
        $nfe = new NotaFiscalEletronica();
        
        $data = [
            'numero_nfe' => $_POST['numero_nfe'],
            'chave_acesso' => $_POST['chave_acesso'],
            'pedido_id' => !empty($_POST['pedido_id']) ? $_POST['pedido_id'] : null,
            'fornecedor_id' => $_POST['fornecedor_id'],
            'data_emissao' => $_POST['data_emissao'],
            'valor_total' => $_POST['valor_total'],
            'observacoes' => $_POST['observacoes'] ?? ''
        ];
        
        if ($nfe->updateNFe($nfeId, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'NF-e atualizada com sucesso',
                'redirect' => DOMAIN . '/recebimento/nfe'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao atualizar NF-e'
            ]);
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
                'error' => 'Sem permissão para excluir NF-e'
            ]);
            return;
        }
        
        $nfeId = $params['id'] ?? null;
        if (!$nfeId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da NF-e não informado'
            ]);
            return;
        }
        
        $nfe = new NotaFiscalEletronica();
        
        if ($nfe->deleteNFe($nfeId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'NF-e excluída com sucesso'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao excluir NF-e'
            ]);
        }
    }

    public function buscarPedido(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $numeroPedido = $_GET['numero'] ?? '';
        if (empty($numeroPedido)) {
            $this->responseJson([
                'success' => false,
                'error' => 'Número do pedido não informado'
            ]);
            return;
        }
        
        $pedidos = new Pedidos();
        $pedido = $pedidos->getPedidoByNumero($numeroPedido);
        
        if ($pedido->getResult()) {
            $this->responseJson([
                'success' => true,
                'pedido' => $pedido->getResult()[0]
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Pedido não encontrado'
            ]);
        }
    }
}
