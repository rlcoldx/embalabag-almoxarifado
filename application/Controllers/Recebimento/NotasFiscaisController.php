<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;
use Agencia\Close\Models\Expedicao\Expedicao;

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

        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }

        $filtros = [
            'numero' => $_GET['numero'] ?? '',
            'fornecedor' => $_GET['fornecedor'] ?? '',
            'status' => $_GET['status'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? '',
            'usuario_recebimento' => $_GET['usuario_recebimento'] ?? ''
        ];

        $notasFiscais = $this->notaFiscal->buscarNotasFiscais($filtros);
        $result = $notasFiscais->getResult() ?? [];

        $this->render('pages/recebimento/notas-fiscais/index.twig', [
            'menu' => 'recebimento_nf',
            'notas_fiscais' => $result,
            'filtros' => $filtros
        ]);
    }

    public function create(array $params = []): void
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'criar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        // Buscar pedidos disponíveis para vinculação
        $pedidos = $this->pedidos->getPedidos();
        $pedidosResult = $pedidos->getResult() ?? [];

        $this->render('pages/recebimento/notas-fiscais/create.twig', [
            'menu' => 'recebimento_nf',
            'pedidos' => $pedidosResult,
            'usuarios_responsaveis' => ResponsavelHelper::listar()
        ]);
    }

    public function store(): void
    {
        $this->checkSession();
        $this->setParams([]);

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
            'data_recebimento' => !empty($_POST['data_entrada']) ? $_POST['data_entrada'] : null,
            'chave_acesso' => $_POST['chave_acesso'] ?? null,
            'valor_total' => $_POST['valor_total'] ?? null,
            'status' => $this->statusNotaFiscalFromPost(),
            'observacoes' => $_POST['observacoes'] ?? null,
            'usuario_recebimento' => ResponsavelHelper::idFromPost('usuario_recebimento', (int) ($_SESSION[BASE . 'user_id'] ?? 0))
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
        $this->checkSession();
        $this->setParams($data);

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

        $itens = $this->notaFiscal->getItensNFByNotaFiscal($id)->getResult() ?: [];

        $this->render('pages/recebimento/notas-fiscais/show.twig', [
            'menu' => 'recebimento_nf',
            'nota_fiscal' => $result[0],
            'itens' => $itens
        ]);
    }

    public function edit(array $data): void
    {
        $this->checkSession();
        $this->setParams($data);

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
            'pedidos' => $pedidosResult,
            'usuarios_responsaveis' => ResponsavelHelper::listar()
        ]);
    }

    public function update(array $data): void
    {
        $this->checkSession();
        $this->setParams($data);

        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'editar')) {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/recebimento/notas-fiscais');
            return;
        }

        $id = (int) $data['id'];

        if (empty($_POST['numero']) || empty($_POST['fornecedor']) || empty($_POST['data_emissao'])) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            $this->redirect("/recebimento/notas-fiscais/edit/{$id}");
            return;
        }

        $data = [
            'numero' => $_POST['numero'],
            'serie' => $_POST['serie'] ?? null,
            'fornecedor' => $_POST['fornecedor'],
            'cnpj_fornecedor' => $_POST['cnpj_fornecedor'] ?? null,
            'pedido_id' => !empty($_POST['pedido_id']) ? $_POST['pedido_id'] : null,
            'data_emissao' => $_POST['data_emissao'],
            'chave_acesso' => $_POST['chave_acesso'] ?? null,
            'valor_total' => $_POST['valor_total'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'usuario_recebimento' => ResponsavelHelper::idFromPost('usuario_recebimento', (int) ($_SESSION[BASE . 'user_id'] ?? 0))
        ];

        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $data['status'] = $_POST['status'];
        }

        $result = $this->notaFiscal->updateNotaFiscal($id, $data);

        if ($result) {
            $_SESSION['success'] = 'Nota fiscal atualizada com sucesso!';
            $this->redirect('/recebimento/notas-fiscais');
        } else {
            $_SESSION['error'] = 'Erro ao atualizar nota fiscal.';
            $this->redirect("/recebimento/notas-fiscais/edit/{$id}");
        }
    }

    public function delete(array $data): void
    {
        $this->checkSession();
        $this->setParams($data);

        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'excluir')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão para excluir notas fiscais.']);
            return;
        }

        $id = (int) $data['id'];
        $result = $this->notaFiscal->deleteNotaFiscal($id);

        $this->responseJson([
            'success' => (bool) $result,
            'message' => $result
                ? 'Nota fiscal excluída com sucesso!'
                : 'Erro ao excluir nota fiscal. Verifique se não há itens vinculados.',
            'redirect' => DOMAIN . '/recebimento/notas-fiscais'
        ]);
    }

    public function receber(array $data): void
    {
        $this->checkSession();
        $this->setParams($data);

        if (!$this->permissionHelper->userHasPermission('notas_fiscais', 'receber')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão para receber notas fiscais.']);
            return;
        }

        $id = (int) $data['id'];
        $usuarioId = $_SESSION[BASE . 'user_id'] ?? 0;

        $result = $this->notaFiscal->marcarComoRecebida($id, $usuarioId);
        if ($result) {
            $this->avisarPedidoDaOf($id);
        }

        $this->responseJson([
            'success' => (bool) $result,
            'message' => $result
                ? 'Nota fiscal marcada como recebida!'
                : 'Erro ao marcar nota fiscal como recebida.',
        ]);
    }

    public function conferir(array $data): void
    {
        $this->checkSession();
        $this->setParams($data);

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
        $this->checkSession();
        $this->setParams($data);

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
            $this->avisarPedidoDaOf($notaFiscalId, $pedidoId);
            $_SESSION['success'] = 'Pedido vinculado à nota fiscal com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao vincular pedido à nota fiscal.';
        }

        $this->redirect('/recebimento/notas-fiscais');
    }

    private function avisarPedidoDaOf(int $notaFiscalId, ?int $pedidoId = null): void
    {
        try {
            $nf = $this->notaFiscal->getNotaFiscalById($notaFiscalId)->getResult()[0] ?? null;
            $pedido = $pedidoId ?: (int)($nf['pedido_id'] ?? 0);
            if ($pedido <= 0) {
                return;
            }
            $numero = $nf['numero'] ?? $notaFiscalId;
            (new Expedicao())->criarAlertaOf(
                $pedido,
                $notaFiscalId,
                'OF/NF ' . $numero . ' entrou e foi direcionada ao pedido.'
            );
        } catch (\Throwable $exception) {
            // A tabela de alertas só existe após a migration 052.
        }
    }

    private function statusNotaFiscalFromPost(): string
    {
        $status = trim($_POST['status'] ?? 'pendente');
        $permitidos = ['pendente', 'recebida', 'conferida', 'finalizada'];

        return in_array($status, $permitidos, true) ? $status : 'pendente';
    }
} 