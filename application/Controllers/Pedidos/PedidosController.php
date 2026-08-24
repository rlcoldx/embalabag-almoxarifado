<?php

namespace Agencia\Close\Controllers\Pedidos;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Models\Pedidos\PedidosItens;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

class PedidosController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }

        $this->render('pages/pedidos/index.twig', [
            'menu' => 'pedidos',
        ]);
    }

    public function create(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'criar')) {
            echo 'Sem permissão para criar pedidos.';
            return;
        }

        $this->render('pages/pedidos/create.twig', [
            'menu' => 'pedidos_new',
            'usuarios_responsaveis' => ResponsavelHelper::listar(),
        ]);
    }

    public function store(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'criar')) {
            $this->responseJson(['success' => false, 'error' => 'Sem permissão para criar pedidos']);
            return;
        }

        $itens = $this->parseItensFromRequest();
        if (empty($itens)) {
            $this->responseJson(['success' => false, 'error' => 'Adicione pelo menos um item ao pedido']);
            return;
        }

        $nomeCliente = trim($_POST['nome_cliente'] ?? '');
        if ($nomeCliente === '') {
            $this->responseJson(['success' => false, 'error' => 'Fornecedor/Solicitante é obrigatório']);
            return;
        }

        $pedidos = new Pedidos();
        $pedidoId = $pedidos->createPedido($this->buildPedidoDataFromPost());

        if (!$pedidoId) {
            $this->responseJson(['success' => false, 'error' => 'Erro ao criar pedido']);
            return;
        }

        $this->salvarItens((int) $pedidoId, $itens);

        $pedidosItens = new PedidosItens();
        $total = $pedidosItens->calcularValorTotal((int) $pedidoId);
        $pedidos->updatePedido((int) $pedidoId, ['valor_total' => number_format($total, 2, '.', '')]);

        $this->responseJson([
            'success' => true,
            'message' => 'Pedido criado com sucesso',
            'redirect' => DOMAIN . '/pedidos/show/' . $pedidoId,
        ]);
    }

    public function show(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'visualizar')) {
            echo 'Sem permissão para visualizar pedidos.';
            return;
        }

        $pedidoId = $this->getPedidoIdFromParams($params);
        if (!$pedidoId) {
            echo 'ID do pedido não informado.';
            return;
        }

        $pedidos = new Pedidos();
        $result = $pedidos->getPedido($pedidoId);
        if (!$result->getResult()) {
            echo 'Pedido não encontrado.';
            return;
        }

        $pedidosItens = new PedidosItens();
        $itens = $pedidosItens->getItensPorPedido($pedidoId)->getResult() ?? [];

        $this->render('pages/pedidos/show.twig', [
            'menu' => 'pedidos',
            'pedido' => $result->getResult()[0],
            'itens' => $itens,
        ]);
    }

    public function edit(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'editar')) {
            echo 'Sem permissão para editar pedidos.';
            return;
        }

        $pedidoId = $this->getPedidoIdFromParams($params);
        if (!$pedidoId) {
            echo 'ID do pedido não informado.';
            return;
        }

        $pedidos = new Pedidos();
        $result = $pedidos->getPedido($pedidoId);
        if (!$result->getResult()) {
            echo 'Pedido não encontrado.';
            return;
        }

        $pedidosItens = new PedidosItens();
        $itens = $pedidosItens->getItensPorPedido($pedidoId)->getResult() ?? [];

        $this->render('pages/pedidos/edit.twig', [
            'menu' => 'pedidos',
            'pedido' => $result->getResult()[0],
            'itens' => $itens,
            'usuarios_responsaveis' => ResponsavelHelper::listar(),
        ]);
    }

    public function update(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'editar')) {
            $this->responseJson(['success' => false, 'error' => 'Sem permissão para editar pedidos']);
            return;
        }

        $pedidoId = $this->getPedidoIdFromParams($params);
        if (!$pedidoId) {
            $this->responseJson(['success' => false, 'error' => 'ID do pedido não informado']);
            return;
        }

        $itens = $this->parseItensFromRequest();
        if (empty($itens)) {
            $this->responseJson(['success' => false, 'error' => 'Adicione pelo menos um item ao pedido']);
            return;
        }

        $nomeCliente = trim($_POST['nome_cliente'] ?? '');
        if ($nomeCliente === '') {
            $this->responseJson(['success' => false, 'error' => 'Fornecedor/Solicitante é obrigatório']);
            return;
        }

        $pedidos = new Pedidos();
        $data = $this->buildPedidoDataFromPost(true);

        if (!$pedidos->updatePedido($pedidoId, $data)) {
            $this->responseJson(['success' => false, 'error' => 'Erro ao atualizar pedido']);
            return;
        }

        $pedidosItens = new PedidosItens();
        $pedidosItens->deleteItensPorPedido($pedidoId);
        $this->salvarItens($pedidoId, $itens);

        $total = $pedidosItens->calcularValorTotal($pedidoId);
        $pedidos->updatePedido($pedidoId, ['valor_total' => number_format($total, 2, '.', '')]);

        $this->responseJson([
            'success' => true,
            'message' => 'Pedido atualizado com sucesso',
            'redirect' => DOMAIN . '/pedidos/show/' . $pedidoId,
        ]);
    }

    public function delete(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'excluir')) {
            $this->responseJson(['success' => false, 'error' => 'Sem permissão para excluir pedidos']);
            return;
        }

        $pedidoId = $this->getPedidoIdFromParams($params);
        if (!$pedidoId) {
            $this->responseJson(['success' => false, 'error' => 'ID do pedido não informado']);
            return;
        }

        $pedidosItens = new PedidosItens();
        $pedidosItens->deleteItensPorPedido($pedidoId);

        $pedidos = new Pedidos();
        if ($pedidos->deletePedido($pedidoId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Pedido excluído com sucesso',
            ]);
        } else {
            $this->responseJson(['success' => false, 'error' => 'Erro ao excluir pedido']);
        }
    }

    public function aprovar(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'aprovar')) {
            $this->responseJson(['success' => false, 'error' => 'Sem permissão para aprovar pedidos']);
            return;
        }

        $pedidoId = $this->getPedidoIdFromParams($params);
        if (!$pedidoId) {
            $this->responseJson(['success' => false, 'error' => 'ID do pedido não informado']);
            return;
        }

        $pedidos = new Pedidos();
        if ($pedidos->aprovarPedido($pedidoId)) {
            $this->responseJson(['success' => true, 'message' => 'Pedido aprovado com sucesso']);
        } else {
            $this->responseJson(['success' => false, 'error' => 'Erro ao aprovar pedido']);
        }
    }

    public function cancelar(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('pedidos', 'cancelar')) {
            $this->responseJson(['success' => false, 'error' => 'Sem permissão para cancelar pedidos']);
            return;
        }

        $pedidoId = $this->getPedidoIdFromParams($params);
        if (!$pedidoId) {
            $this->responseJson(['success' => false, 'error' => 'ID do pedido não informado']);
            return;
        }

        $pedidos = new Pedidos();
        if ($pedidos->cancelarPedido($pedidoId)) {
            $this->responseJson(['success' => true, 'message' => 'Pedido cancelado com sucesso']);
        } else {
            $this->responseJson(['success' => false, 'error' => 'Erro ao cancelar pedido']);
        }
    }

    private function getPedidoIdFromParams(array $params): ?int
    {
        $id = $params['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            return null;
        }
        return (int) $id;
    }

    private function buildPedidoDataFromPost(bool $includeStatus = false): array
    {
        $data = [
            'nome_cliente' => trim($_POST['nome_cliente'] ?? ''),
            'base_solicitante' => trim($_POST['base_solicitante'] ?? 'Base Principal'),
            'base_destino' => trim($_POST['base_destino'] ?? '') ?: null,
            'previsao_entrega' => trim($_POST['previsao_entrega'] ?? '') ?: null,
            'prioridade' => trim($_POST['prioridade'] ?? 'Normal'),
            'observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
            'id_user' => ResponsavelHelper::idFromPost('id_user', (int) ($_SESSION[BASE . 'user_id'] ?? 0)),
            'nome_colaborador' => ResponsavelHelper::nomePorId(ResponsavelHelper::idFromPost('id_user', (int) ($_SESSION[BASE . 'user_id'] ?? 0))) ?: null,
        ];

        if ($includeStatus && isset($_POST['status_pedido'])) {
            $data['status_pedido'] = trim($_POST['status_pedido']);
        }

        return $data;
    }

    private function parseItensFromRequest(): array
    {
        $itensRaw = $_POST['itens'] ?? '';
        if (is_string($itensRaw)) {
            $decoded = json_decode($itensRaw, true);
            if (!is_array($decoded)) {
                return [];
            }
            $itensRaw = $decoded;
        }

        if (!is_array($itensRaw)) {
            return [];
        }

        $itens = [];
        foreach ($itensRaw as $item) {
            $idProduto = (int) ($item['id_produto'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);
            $valorUnidade = (float) ($item['valor_unidade'] ?? 0);

            if ($idProduto <= 0 || $qty <= 0) {
                continue;
            }

            $itens[] = [
                'id_produto' => $idProduto,
                'qty' => $qty,
                'valor_unidade' => number_format($valorUnidade, 2, '.', ''),
                'valor_total' => number_format($qty * $valorUnidade, 2, '.', ''),
            ];
        }

        return $itens;
    }

    private function salvarItens(int $pedidoId, array $itens): void
    {
        $pedidosItens = new PedidosItens();
        foreach ($itens as $item) {
            $pedidosItens->createItem(array_merge($item, ['id_pedido' => $pedidoId]));
        }
    }
}
