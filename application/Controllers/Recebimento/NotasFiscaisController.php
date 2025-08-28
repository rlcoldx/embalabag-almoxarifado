<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Helpers\User\PermissionHelper;

class NotasFiscaisController extends Controller
{
    private NotaFiscal $notaFiscal;
    private Pedidos $pedidos;
    private PermissionHelper $permissionHelper;

    public function __construct($router = null)
    {
        parent::__construct($router);
        $this->notaFiscal = new NotaFiscal();
        $this->pedidos = new Pedidos();
        $this->permissionHelper = new PermissionHelper();
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . DOMAIN . $url);
        exit;
    }

    public function index(array $params = []): void
    {
        $this->checkSession();
        $this->setParams($params);
        
        // Temporariamente comentado para testar o menu
        // if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'visualizar')) {
        //     $this->redirect('/home');
        //     return;
        // }

        $filtros = [
            'numero' => $_GET['numero'] ?? '',
            'fornecedor' => $_GET['fornecedor'] ?? '',
            'status' => $_GET['status'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? ''
        ];

        $notasFiscais = $this->notaFiscal->buscarNotasFiscais($filtros);
        $result = $notasFiscais->getResult() ?? [];

        $this->render('pages/recebimento/notas-fiscais/index.twig', [
            'menu' => 'recebimento_nf',
            'notas_fiscais' => $result,
            'filtros' => $filtros
        ]);
    }

    public function create(): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'criar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        // Buscar pedidos disponíveis para vinculação
        $pedidos = $this->pedidos->getPedidos();
        $pedidosResult = $pedidos->getResult() ?? [];

        $this->render('pages/recebimento/notas-fiscais/create.twig', [
            'menu' => 'recebimento_nf',
            'pedidos' => $pedidosResult
        ]);
    }

    public function store(): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'criar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/recebimento/notas-fiscais/create');
            return;
        }

        // Validar dados obrigatórios
        if (empty($_POST['numero']) || empty($_POST['fornecedor']) || empty($_POST['data_emissao'])) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            $this->redirect('/recebimento/notas-fiscais/create');
            return;
        }

        // Verificar se já existe NF com mesmo número e série
        $notaExistente = $this->notaFiscal->getNotaFiscalByNumero($_POST['numero'], $_POST['serie'] ?? null);
        if ($notaExistente->getResult()) {
            $_SESSION['error'] = 'Já existe uma nota fiscal com este número e série.';
            $this->redirect('/recebimento/notas-fiscais/create');
            return;
        }

        $data = [
            'numero' => $_POST['numero'],
            'serie' => $_POST['serie'] ?? null,
            'fornecedor' => $_POST['fornecedor'],
            'cnpj_fornecedor' => $_POST['cnpj_fornecedor'] ?? null,
            'pedido_id' => !empty($_POST['pedido_id']) ? $_POST['pedido_id'] : null,
            'data_emissao' => $_POST['data_emissao'],
            'valor_total' => $_POST['valor_total'] ?? null,
            'status' => 'pendente',
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        $result = $this->notaFiscal->createNotaFiscal($data);

        if ($result) {
            $_SESSION['success'] = 'Nota fiscal criada com sucesso!';
            $this->redirect('/recebimento/notas-fiscais');
        } else {
            $_SESSION['error'] = 'Erro ao criar nota fiscal.';
            $this->redirect('/recebimento/notas-fiscais/create');
        }
    }

    public function show(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'visualizar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];
        $notaFiscal = $this->notaFiscal->getNotaFiscalById($id);
        $result = $notaFiscal->getResult();

        if (!$result) {
            $_SESSION['error'] = 'Nota fiscal não encontrada.';
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $this->render('pages/recebimento/notas-fiscais/show.twig', [
            'menu' => 'recebimento_nf',
            'nota_fiscal' => $result[0]
        ]);
    }

    public function edit(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'editar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];
        $notaFiscal = $this->notaFiscal->getNotaFiscalById($id);
        $result = $notaFiscal->getResult();

        if (!$result) {
            $_SESSION['error'] = 'Nota fiscal não encontrada.';
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        // Buscar pedidos disponíveis para vinculação
        $pedidos = $this->pedidos->getPedidos();
        $pedidosResult = $pedidos->getResult() ?? [];

        $this->render('pages/recebimento/notas-fiscais/edit.twig', [
            'menu' => 'recebimento_nf',
            'nota_fiscal' => $result[0],
            'pedidos' => $pedidosResult
        ]);
    }

    public function update(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'editar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];

        // Validar dados obrigatórios
        if (empty($_POST['numero']) || empty($_POST['fornecedor']) || empty($_POST['data_emissao'])) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            $this->redirect("/recebimento/notas-fiscais/{$id}/edit");
            return;
        }

        $data = [
            'numero' => $_POST['numero'],
            'serie' => $_POST['serie'] ?? null,
            'fornecedor' => $_POST['fornecedor'],
            'cnpj_fornecedor' => $_POST['cnpj_fornecedor'] ?? null,
            'pedido_id' => !empty($_POST['pedido_id']) ? $_POST['pedido_id'] : null,
            'data_emissao' => $_POST['data_emissao'],
            'valor_total' => $_POST['valor_total'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        $result = $this->notaFiscal->updateNotaFiscal($id, $data);

        if ($result) {
            $_SESSION['success'] = 'Nota fiscal atualizada com sucesso!';
            $this->redirect('/recebimento/notas-fiscais');
        } else {
            $_SESSION['error'] = 'Erro ao atualizar nota fiscal.';
            $this->redirect("/recebimento/notas-fiscais/{$id}/edit");
        }
    }

    public function delete(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'excluir')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];
        $result = $this->notaFiscal->deleteNotaFiscal($id);

        if ($result) {
            $_SESSION['success'] = 'Nota fiscal excluída com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao excluir nota fiscal. Verifique se não há itens vinculados.';
        }

        $this->redirect('/recebimento/notas-fiscais');
    }

    public function receber(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'receber')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];
        $usuarioId = $_SESSION[BASE . 'user_id'] ?? 0;

        $result = $this->notaFiscal->marcarComoRecebida($id, $usuarioId);

        if ($result) {
            $_SESSION['success'] = 'Nota fiscal marcada como recebida!';
        } else {
            $_SESSION['error'] = 'Erro ao marcar nota fiscal como recebida.';
        }

        $this->redirect('/recebimento/notas-fiscais');
    }

    public function conferir(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'receber')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];
        $usuarioId = $_SESSION[BASE . 'user_id'] ?? 0;

        $result = $this->notaFiscal->marcarComoConferida($id, $usuarioId);

        if ($result) {
            $_SESSION['success'] = 'Nota fiscal marcada como conferida!';
        } else {
            $_SESSION['error'] = 'Erro ao marcar nota fiscal como conferida.';
        }

        $this->redirect('/recebimento/notas-fiscais');
    }

    public function vincularPedido(array $data): void
    {
        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'editar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $notaFiscalId = (int) $data['id'];
        $pedidoId = (int) $_POST['pedido_id'];

        $result = $this->notaFiscal->vincularPedido($notaFiscalId, $pedidoId);

        if ($result) {
            $_SESSION['success'] = 'Pedido vinculado à nota fiscal com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao vincular pedido à nota fiscal.';
        }

        $this->redirect('/recebimento/notas-fiscais');
    }
} 