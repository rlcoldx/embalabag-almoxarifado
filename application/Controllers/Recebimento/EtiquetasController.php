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
        
        $this->render('pages/recebimento/etiquetas/create.twig', [
            'menu' => 'recebimento_etiquetas'
        ]);
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
        
        $data = [
            'codigo' => $_POST['codigo'] ?? '',
            'tipo_etiqueta' => $_POST['tipo_etiqueta'] ?? '',
            'conteudo' => $_POST['conteudo'] ?? '',
            'qr_code_data' => $_POST['qr_code_data'] ?? '',
            'status' => 'ativa',
            'usuario_criacao' => $_SESSION['user_id'] ?? null,
            'data_impressao' => null
        ];
        
        if ($etiqueta->create($data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta criada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao criar etiqueta.'
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
        
        $id = $params['id'] ?? null;
        if (!$id) {
            echo 'ID da etiqueta não informado.';
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        $dados = $etiqueta->getById($id);
        
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
        
        $id = $params['id'] ?? null;
        if (!$id) {
            echo 'ID da etiqueta não informado.';
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        $dados = $etiqueta->getById($id);
        
        if (!$dados) {
            echo 'Etiqueta não encontrada.';
            return;
        }
        
        $this->render('pages/recebimento/etiquetas/edit.twig', [
            'menu' => 'recebimento_etiquetas',
            'etiqueta' => $dados
        ]);
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
        if (!$id) {
            $this->responseJson([
                'success' => false,
                'message' => 'ID da etiqueta não informado.'
            ]);
            return;
        }
        
        $etiqueta = new EtiquetaInterna();
        
        $data = [
            'codigo' => $_POST['codigo'] ?? '',
            'tipo_etiqueta' => $_POST['tipo_etiqueta'] ?? '',
            'conteudo' => $_POST['conteudo'] ?? '',
            'qr_code_data' => $_POST['qr_code_data'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($etiqueta->update($id, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta atualizada com sucesso!'
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
        
        $data = [
            'codigo' => 'LOC' . $dados['codigo'],
            'tipo_etiqueta' => 'localizacao',
            'conteudo' => $dados['descricao'] . ' - ' . $dados['codigo'],
            'status' => 'ativa',
            'usuario_criacao' => $_SESSION['user_id'] ?? null
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
        if (!$permissionHelper->userHasPermission('etiqueta', 'criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para gerar etiquetas.'
            ]);
            return;
        }
        
        $itemNfId = $params['item_nf_id'] ?? null;
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
        
        $item = $item[0] ?? null;
        
        $etiqueta = new EtiquetaInterna();
        
        $data = [
            'codigo' => 'PRO' . $item['codigo_produto'],
            'tipo_etiqueta' => 'produto',
            'conteudo' => $item['descricao_produto'] . ' - Qtd: ' . $item['quantidade'],
            'status' => 'ativa',
            'usuario_criacao' => $_SESSION['user_id'] ?? null
        ];
        
        if ($etiqueta->create($data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta de produto gerada com sucesso!'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'message' => 'Erro ao gerar etiqueta de produto.'
            ]);
        }
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
        
        $etiqueta = new EtiquetaInterna();
        $geradas = 0;
        
        foreach ($armazenagens as $arm) {
            $data = [
                'codigo' => 'LOC' . $arm['codigo'],
                'tipo_etiqueta' => 'localizacao',
                'conteudo' => $arm['descricao'] . ' - ' . $arm['codigo'],
                'status' => 'ativa',
                'usuario_criacao' => $_SESSION['user_id'] ?? null
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
        
        $etiqueta = new EtiquetaInterna();
        $geradas = 0;
        
        foreach ($itens as $item) {
            $data = [
                'codigo' => 'PRO' . $item['codigo_produto'],
                'tipo_etiqueta' => 'produto',
                'conteudo' => $item['descricao_produto'] . ' - Qtd: ' . $item['quantidade'],
                'status' => 'ativa',
                'usuario_criacao' => $_SESSION['user_id'] ?? null
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
} 