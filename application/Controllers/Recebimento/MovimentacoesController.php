<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Movimentacao\MovimentacaoInterna;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

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

        $this->render('pages/recebimento/movimentacoes/create.twig', array_merge(
            $this->getFormLists(),
            ['menu' => 'recebimento_movimentacoes']
        ));
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

        $data = $this->montarDadosMovimentacao($_POST);
        $erro = $this->validarDadosMovimentacao($data);
        if ($erro) {
            $this->responseJson(['success' => false, 'message' => $erro]);
            return;
        }

        $executarAgora = ($_POST['status'] ?? '') === 'concluida' || !empty($_POST['executar_agora']);
        $data['status'] = 'pendente';

        $movimentacao = new MovimentacaoInterna();
        $id = $movimentacao->create($data);

        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao criar movimentação.'
            ]);
            return;
        }

        if ($executarAgora && !$movimentacao->executar((int)$id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação criada, mas não foi possível executá-la automaticamente.',
                'redirect' => DOMAIN . '/recebimento/movimentacoes/show/' . $id
            ]);
            return;
        }

        $this->responseJson([
            'success' => true,
            'message' => $executarAgora ? 'Movimentação criada e executada com sucesso!' : 'Movimentação criada com sucesso!',
            'redirect' => DOMAIN . '/recebimento/movimentacoes/show/' . $id
        ]);
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

        $dados = $this->getMovimentacaoOrFail($params['id'] ?? null);
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

        $dados = $this->getMovimentacaoOrFail($params['id'] ?? null);
        if (!$dados) {
            echo 'Movimentação não encontrada.';
            return;
        }

        $this->render('pages/recebimento/movimentacoes/edit.twig', array_merge(
            $this->getFormLists(),
            [
                'menu' => 'recebimento_movimentacoes',
                'movimentacao' => $dados
            ]
        ));
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
        $atual = $this->getMovimentacaoOrFail($id);
        if (!$atual) {
            $this->responseJson([
                'success' => false,
                'message' => 'Movimentação não encontrada.'
            ]);
            return;
        }

        if (in_array($atual['status'], ['concluida', 'cancelada'], true)) {
            $this->responseJson([
                'success' => false,
                'message' => 'Não é possível editar uma movimentação concluída ou cancelada.'
            ]);
            return;
        }

        $data = [
            'tipo_movimentacao' => $_POST['tipo_movimentacao'] ?? $atual['tipo_movimentacao'],
            'armazenagem_origem_id' => $this->toNullableInt($_POST['armazenagem_origem_id'] ?? null),
            'armazenagem_destino_id' => $this->toNullableInt($_POST['armazenagem_destino_id'] ?? null),
            'quantidade_movimentada' => (int)($_POST['quantidade_movimentada'] ?? 0),
            'motivo' => trim($_POST['motivo'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];

        $erro = $this->validarDadosMovimentacao(array_merge($data, [
            'item_nf_id' => $atual['item_nf_id']
        ]));
        if ($erro) {
            $this->responseJson(['success' => false, 'message' => $erro]);
            return;
        }

        $movimentacao = new MovimentacaoInterna();
        if ($movimentacao->update((int)$id, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação atualizada com sucesso!',
                'redirect' => DOMAIN . '/recebimento/movimentacoes/show/' . $id
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

        if ($movimentacao->delete((int)$id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação excluída com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao excluir movimentação. Movimentações concluídas não podem ser excluídas.'
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

        if ($movimentacao->executar((int)$id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Movimentação executada com sucesso!',
                'redirect' => DOMAIN . '/recebimento/movimentacoes/show/' . $id
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao executar movimentação. Verifique se ela está pendente.'
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

        $this->render('pages/recebimento/movimentacoes/put-away.twig', array_merge(
            $this->getFormLists(),
            ['menu' => 'recebimento_movimentacoes']
        ));
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

        $data = $this->montarDadosMovimentacao($_POST, [
            'tipo_movimentacao' => 'put_away',
            'armazenagem_origem_id' => null,
            'motivo' => trim($_POST['motivo'] ?? '') ?: 'Put-away de recebimento',
        ]);

        $erro = $this->validarDadosMovimentacao($data);
        if ($erro) {
            $this->responseJson(['success' => false, 'message' => $erro]);
            return;
        }

        $movimentacao = new MovimentacaoInterna();
        $id = $movimentacao->realizarPutAway($data);

        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao realizar put-away. Verifique o item e a armazenagem de destino.'
            ]);
            return;
        }

        $movimentacao->executar((int)$id);

        $this->responseJson([
            'success' => true,
            'message' => 'Put-away realizado com sucesso!',
            'redirect' => DOMAIN . '/recebimento/movimentacoes/show/' . $id
        ]);
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

        $this->render('pages/recebimento/movimentacoes/transferencia.twig', array_merge(
            $this->getFormLists(),
            ['menu' => 'recebimento_movimentacoes']
        ));
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

        $data = $this->montarDadosMovimentacao($_POST, [
            'tipo_movimentacao' => 'transferencia',
            'motivo' => trim($_POST['motivo'] ?? '') ?: 'Transferência entre armazenagens',
        ]);

        $erro = $this->validarDadosMovimentacao($data, true);
        if ($erro) {
            $this->responseJson(['success' => false, 'message' => $erro]);
            return;
        }

        $data['status'] = 'pendente';

        $movimentacao = new MovimentacaoInterna();
        $id = $movimentacao->create($data);

        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao realizar transferência.'
            ]);
            return;
        }

        $movimentacao->executar((int)$id);

        $this->responseJson([
            'success' => true,
            'message' => 'Transferência realizada com sucesso!',
            'redirect' => DOMAIN . '/recebimento/movimentacoes/show/' . $id
        ]);
    }

    private function getFormLists(): array
    {
        $armazenagem = new Armazenagem();
        $notaFiscal = new NotaFiscal();

        return [
            'armazenagens' => $armazenagem->getArmazenagensAtivas()->getResult() ?: [],
            'itens' => $notaFiscal->getItensParaMovimentacao()->getResult() ?: [],
            'notas' => $notaFiscal->getAllNotasFiscais()->getResult() ?: [],
            'usuarios_responsaveis' => ResponsavelHelper::listar(),
        ];
    }

    private function getMovimentacaoOrFail($id): ?array
    {
        if (!$id) {
            return null;
        }

        $movimentacao = new MovimentacaoInterna();
        $result = $movimentacao->getById((int)$id)->getResult();

        return $result ? $result[0] : null;
    }

    private function montarDadosMovimentacao(array $post, array $overrides = []): array
    {
        $data = [
            'item_nf_id' => $this->toNullableInt($post['item_nf_id'] ?? null),
            'tipo_movimentacao' => $overrides['tipo_movimentacao'] ?? ($post['tipo_movimentacao'] ?? ''),
            'armazenagem_origem_id' => array_key_exists('armazenagem_origem_id', $overrides)
                ? $overrides['armazenagem_origem_id']
                : $this->toNullableInt($post['armazenagem_origem_id'] ?? null),
            'armazenagem_destino_id' => $this->toNullableInt($post['armazenagem_destino_id'] ?? null),
            'quantidade_movimentada' => (int)($post['quantidade_movimentada'] ?? 0),
            'motivo' => $overrides['motivo'] ?? trim($post['motivo'] ?? ''),
            'observacoes' => trim($post['observacoes'] ?? ''),
            'usuario_movimentacao' => ResponsavelHelper::idFromPost('usuario_movimentacao', (int) ($_SESSION[BASE . 'user_id'] ?? 0)),
            'data_movimentacao' => date('Y-m-d H:i:s'),
            'status' => 'pendente',
        ];

        return $data;
    }

    private function validarDadosMovimentacao(array $data, bool $origemObrigatoria = false): ?string
    {
        if (empty($data['item_nf_id'])) {
            return 'Selecione o item da nota fiscal.';
        }

        if (empty($data['tipo_movimentacao'])) {
            return 'Selecione o tipo de movimentação.';
        }

        if (empty($data['armazenagem_destino_id'])) {
            return 'Selecione a armazenagem de destino.';
        }

        if ($data['quantidade_movimentada'] <= 0) {
            return 'A quantidade movimentada deve ser maior que zero.';
        }

        $precisaOrigem = $origemObrigatoria || in_array($data['tipo_movimentacao'], ['transferencia', 'reposicao'], true);
        if ($precisaOrigem && empty($data['armazenagem_origem_id'])) {
            return 'Selecione a armazenagem de origem.';
        }

        if (!empty($data['armazenagem_origem_id']) && (int)$data['armazenagem_origem_id'] === (int)$data['armazenagem_destino_id']) {
            return 'A armazenagem de origem e destino não podem ser iguais.';
        }

        if (empty($data['usuario_movimentacao'])) {
            return 'Usuário da movimentação não identificado.';
        }

        return null;
    }

    private function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }
}
