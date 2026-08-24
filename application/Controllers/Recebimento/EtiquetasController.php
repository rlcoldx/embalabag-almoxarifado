<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Etiqueta\EtiquetaInterna;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Helpers\User\PermissionHelper;

class EtiquetasController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $this->render('pages/recebimento/etiquetas/index.twig', [
            'menu' => 'recebimento_etiquetas'
        ]);
    }

    public function create(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'criar')) {
            echo 'Sem permissão para criar etiquetas.';
            return;
        }
        
        $this->render('pages/recebimento/etiquetas/create.twig', array_merge(
            $this->getFormLists(),
            ['menu' => 'recebimento_etiquetas']
        ));
    }

    public function store(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para criar etiquetas.'
            ]);
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        $data = $this->montarDadosEtiqueta($_POST, $etiqueta);

        $erro = $this->validarDadosEtiqueta($data);
        if ($erro) {
            $this->responseJson(['success' => false, 'message' => $erro]);
            return;
        }

        $id = $etiqueta->create($data);
        if ($id) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta criada com sucesso!',
                'redirect' => DOMAIN . '/recebimento/etiquetas/show/' . $id
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao criar etiqueta. Verifique se o código já existe.'
            ]);
        }
    }

    public function show(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'visualizar')) {
            echo 'Sem permissão para visualizar etiquetas.';
            return;
        }
        
        $dados = $this->getEtiquetaOrFail($params['id'] ?? null);
        if (!$dados) {
            echo 'Etiqueta não encontrada.';
            return;
        }

        $this->render('pages/recebimento/etiquetas/show.twig', [
            'menu' => 'recebimento_etiquetas',
            'etiqueta' => $dados
        ]);
    }

    public function edit(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'editar')) {
            echo 'Sem permissão para editar etiquetas.';
            return;
        }
        
        $dados = $this->getEtiquetaOrFail($params['id'] ?? null);
        if (!$dados) {
            echo 'Etiqueta não encontrada.';
            return;
        }

        $this->render('pages/recebimento/etiquetas/edit.twig', array_merge(
            $this->getFormLists(),
            [
                'menu' => 'recebimento_etiquetas',
                'etiqueta' => $dados
            ]
        ));
    }

    public function update(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'editar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para editar etiquetas.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        $atual = $this->getEtiquetaOrFail($id);
        if (!$atual) {
            $this->responseJson([
                'success' => false,
                'message' => 'Etiqueta não encontrada.'
            ]);
            return;
        }

        $etiqueta = new EtiquetaInterna();
        $data = $this->montarDadosEtiqueta($_POST, $etiqueta, $atual);

        $erro = $this->validarDadosEtiqueta($data, false);
        if ($erro) {
            $this->responseJson(['success' => false, 'message' => $erro]);
            return;
        }

        unset($data['usuario_criacao']);

        if ($etiqueta->update((int)$id, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta atualizada com sucesso!',
                'redirect' => DOMAIN . '/recebimento/etiquetas/show/' . $id
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao atualizar etiqueta.'
            ]);
        }
    }

    public function delete(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'excluir')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para excluir etiquetas.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da etiqueta não informado.'
            ]);
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        
        if ($etiqueta->delete($id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta excluída com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao excluir etiqueta.'
            ]);
        }
    }

    public function imprimir(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'imprimir')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para imprimir etiquetas.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da etiqueta não informado.'
            ]);
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        
        if ($etiqueta->marcarComoImpressa($id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta enviada para impressão!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao imprimir etiqueta.'
            ]);
        }
    }

    public function aplicar(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'aplicar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para aplicar etiquetas.'
            ]);
            return;
        }
        
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da etiqueta não informado.'
            ]);
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        
        if ($etiqueta->aplicar($id)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta aplicada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao aplicar etiqueta.'
            ]);
        }
    }

    public function gerarEtiquetaLocalizacao(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para gerar etiquetas.'
            ]);
            return;
        }
        
        $armazenagemId = $params['armazenagem_id'] ?? null;
        if (!$armazenagemId) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da armazenagem não informado.'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        $result = $armazenagem->getArmazenagemById($armazenagemId);
        $dados = $result->getResult();
        
        if (!$dados) {
            $this->responseJson([
                'success' => false,
                'message' => 'Armazenagem não encontrada.'
            ]);
            return;
        }
        
        $dados = $dados[0] ?? null;
        
        $etiqueta = new EtiquetaInterna();
        
        $usuarioId = (int)($_SESSION[BASE.'user_id'] ?? 0);
        if ($usuarioId <= 0) {
            $this->responseJson([
                'success' => false,
                'message' => 'Usuário da etiqueta não identificado.'
            ]);
            return;
        }

        $data = [
            'codigo' => 'LOC' . $dados['codigo'],
            'tipo_etiqueta' => 'localizacao',
            'conteudo' => $dados['descricao'] . ' - ' . $dados['codigo'],
            'qr_code' => 'LOC' . $dados['codigo'],
            'codigo_barras' => 'LOC' . $dados['codigo'],
            'status' => 'criada',
            'usuario_criacao' => $usuarioId
        ];
        
        if ($etiqueta->create($data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta de localização gerada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao gerar etiqueta de localização.'
            ]);
        }
    }

    public function gerarEtiquetaProduto(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (
            !$permissionHelper->userHasPermission('etiqueta', 'criar')
            && !$permissionHelper->userHasPermission('etiquetas', 'criar')
        ) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para gerar etiquetas.'
            ]);
            return;
        }

        $itemNfId = $params['item_nf_id']
            ?? $params['item_id']
            ?? $_POST['item_id']
            ?? $_POST['item_nf_id']
            ?? $_GET['item_id']
            ?? null;
        if (!$itemNfId) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID do item da NF não informado.'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $result = $notaFiscal->getItemNFById($itemNfId);
        $item = $result->getResult();
        
        if (!$item) {
            $this->responseJson([
                'success' => false,
                'message' => 'Item da NF não encontrado.'
            ]);
            return;
        }
        
        $usuarioId = (int)($_SESSION[BASE . 'user_id'] ?? 0);
        $etiqueta = new EtiquetaInterna();
        $etiquetaId = $etiqueta->criarEtiquetaProduto((int)$itemNfId, $usuarioId);

        if ($etiquetaId) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta de produto gerada com sucesso.',
                'etiqueta_id' => $etiquetaId,
                'redirect' => DOMAIN . '/recebimento/etiquetas/show/' . $etiquetaId
            ]);
            return;
        }

        $this->responseJson([
            'success' => false,
            'message' => 'Erro ao gerar etiqueta de produto.'
        ]);
    }

    public function gerarLoteArmazenagens(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para gerar etiquetas.'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        $result = $armazenagem->getAllArmazenagens();
        $armazenagens = $result->getResult();
        
        $usuarioId = (int)($_SESSION[BASE.'user_id'] ?? 0);
        if ($usuarioId <= 0) {
            $this->responseJson([
                'success' => false,
                'message' => 'Usuário da etiqueta não identificado.'
            ]);
            return;
        }

        $etiqueta = new EtiquetaInterna();
        $geradas = 0;
        
        foreach ($armazenagens as $arm) {
            $data = [
                'codigo' => 'LOC' . $arm['codigo'],
                'tipo_etiqueta' => 'localizacao',
                'conteudo' => $arm['descricao'] . ' - ' . $arm['codigo'],
                'qr_code' => 'LOC' . $arm['codigo'],
                'codigo_barras' => 'LOC' . $arm['codigo'],
                'status' => 'criada',
                'usuario_criacao' => $usuarioId
            ];
            
            if ($etiqueta->create($data)) {
                $geradas++;
            }
        }
        
        $this->responseJson([
            'success' => true,
            'message' => $geradas . ' etiquetas de armazenagem geradas com sucesso!'
        ]);
    }

    public function gerarLoteProdutos(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('etiqueta', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para gerar etiquetas.'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $result = $notaFiscal->getAllItens();
        $itens = $result->getResult();
        
        $usuarioId = (int)($_SESSION[BASE.'user_id'] ?? 0);
        if ($usuarioId <= 0) {
            $this->responseJson([
                'success' => false,
                'message' => 'Usuário da etiqueta não identificado.'
            ]);
            return;
        }

        $etiqueta = new EtiquetaInterna();
        $geradas = 0;
        
        foreach ($itens as $item) {
            $data = [
                'codigo' => 'PRO' . $item['codigo_produto'],
                'tipo_etiqueta' => 'produto',
                'conteudo' => $item['descricao_produto'] . ' - Qtd: ' . $item['quantidade'],
                'qr_code' => 'PRO' . $item['codigo_produto'],
                'codigo_barras' => 'PRO' . $item['codigo_produto'],
                'status' => 'criada',
                'usuario_criacao' => $usuarioId
            ];
            
            if ($etiqueta->create($data)) {
                $geradas++;
            }
        }
        
        $this->responseJson([
            'success' => true,
            'message' => $geradas . ' etiquetas de produtos geradas com sucesso!'
        ]);
    }

    private function getFormLists(): array
    {
        $armazenagem = new Armazenagem();
        $notaFiscal = new NotaFiscal();

        $itens = method_exists($notaFiscal, 'getItensParaMovimentacao')
            ? $notaFiscal->getItensParaMovimentacao()->getResult()
            : $notaFiscal->getAllItens()->getResult();

        return [
            'armazenagens' => $armazenagem->getArmazenagensAtivas()->getResult() ?: [],
            'itens' => $itens ?: [],
        ];
    }

    private function getEtiquetaOrFail($id): ?array
    {
        if (!$id) {
            return null;
        }

        $etiqueta = new EtiquetaInterna();
        $result = $etiqueta->getById((int)$id)->getResult();
        if (!$result) {
            return null;
        }

        $dados = $result[0];
        $decoded = json_decode($dados['conteudo'] ?? '', true);
        $dados['conteudo_json'] = is_array($decoded) ? $decoded : null;
        $dados['qr_code_payload'] = $etiqueta->payloadQrCode($dados);
        $dados['qr_code_image'] = $etiqueta->gerarQRCode($dados['qr_code_payload']);

        return $dados;
    }

    private function montarDadosEtiqueta(array $post, EtiquetaInterna $etiqueta, ?array $atual = null): array
    {
        $tipo = $post['tipo_etiqueta'] ?? ($atual['tipo_etiqueta'] ?? '');
        $codigo = trim($post['codigo'] ?? '');
        if ($codigo === '') {
            $codigo = $atual['codigo'] ?? $etiqueta->gerarCodigoEtiqueta($tipo ?: 'produto');
        }

        $statusPermitidos = ['criada', 'impressa', 'aplicada', 'inativa'];
        $status = $post['status'] ?? ($atual['status'] ?? 'criada');
        if (!in_array($status, $statusPermitidos, true)) {
            $status = 'criada';
        }

        $qrCode = trim($post['qr_code'] ?? ($post['qr_code_data'] ?? ''));
        if ($qrCode === '' || strpos($qrCode, 'data:image') === 0) {
            $qrCode = $codigo;
        }

        return [
            'codigo' => $codigo,
            'tipo_etiqueta' => $tipo,
            'referencia_tipo' => $this->emptyToNull($post['referencia_tipo'] ?? null),
            'referencia_id' => $this->toNullableInt($post['referencia_id'] ?? null),
            'conteudo' => trim($post['conteudo'] ?? ''),
            'codigo_barras' => $this->emptyToNull($post['codigo_barras'] ?? null),
            'qr_code' => $this->emptyToNull($qrCode),
            'observacoes' => $this->emptyToNull($post['observacoes'] ?? null),
            'status' => $status,
            'usuario_criacao' => $_SESSION[BASE . 'user_id'] ?? null,
        ];
    }

    private function validarDadosEtiqueta(array $data, bool $exigeUsuario = true): ?string
    {
        if ($data['codigo'] === '') {
            return 'O código da etiqueta é obrigatório.';
        }

        if ($data['tipo_etiqueta'] === '') {
            return 'Selecione o tipo de etiqueta.';
        }

        if ($data['conteudo'] === '') {
            return 'O conteúdo da etiqueta é obrigatório.';
        }

        if ($exigeUsuario && empty($data['usuario_criacao'])) {
            return 'Usuário da etiqueta não identificado.';
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

    private function emptyToNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }
} 