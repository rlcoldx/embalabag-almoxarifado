<?php

namespace Agencia\Close\Controllers\Armazenagens;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Helpers\User\PermissionHelper;

class ArmazenagensController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession(); // Verificar sessão antes de renderizar
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $armazenagem = new Armazenagem();
        $armazenagens = $armazenagem->getAllArmazenagens();
        
        $this->render('pages/armazenagens/index.twig', [
            'armazenagens' => $armazenagens->getResult() ?? []
        ]);
    }

    public function create(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'criar')) {
            echo 'Sem permissão para criar armazenagens.';
            return;
        }
        
        $this->render('pages/armazenagens/create.twig');
    }

    public function store(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'criar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para criar armazenagens'
            ]);
            return;
        }
        
        $data = [
            'codigo' => $_POST['codigo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'tipo' => $_POST['tipo'] ?? '',
            'setor' => $_POST['setor'] ?? '',
            'capacidade_maxima' => $_POST['capacidade_maxima'] ?? 0,
            'status' => $_POST['status'] ?? 'ativo',
            'observacoes' => $_POST['observacoes'] ?? ''
        ];
        
        if (empty($data['codigo'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Código é obrigatório'
            ]);
            return;
        }
        
        if (empty($data['descricao'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Descrição é obrigatória'
            ]);
            return;
        }
        
        if (empty($data['tipo'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Tipo é obrigatório'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        
        $armazenagemId = $armazenagem->createArmazenagem($data);
        if ($armazenagemId) {
            $this->responseJson([
                'success' => true,
                'message' => 'Armazenagem criada com sucesso',
                'redirect' => DOMAIN . '/armazenagens'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao criar armazenagem'
            ]);
        }
    }

    public function edit(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'editar')) {
            echo 'Sem permissão para editar armazenagens.';
            return;
        }
        
        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            echo 'ID da armazenagem não informado.';
            return;
        }
        
        $armazenagem = new Armazenagem();
        $armazenagemData = $armazenagem->getArmazenagemById($armazenagemId);
        if (!$armazenagemData->getResult()) {
            echo 'Armazenagem não encontrada.';
            return;
        }
        
        $this->render('pages/armazenagens/edit.twig', [
            'armazenagem' => $armazenagemData->getResult()[0]
        ]);
    }

    public function update(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'editar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para editar armazenagens'
            ]);
            return;
        }
        
        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da armazenagem não informado'
            ]);
            return;
        }        
        
        // Validar se o ID é numérico
        if (!is_numeric($armazenagemId)) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da armazenagem deve ser numérico'
            ]);
            return;
        }
        
        // Converter para inteiro
        $armazenagemId = (int) $armazenagemId;
        
        $data = [
            'codigo' => $_POST['codigo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'tipo' => $_POST['tipo'] ?? '',
            'setor' => $_POST['setor'] ?? '',
            'capacidade_maxima' => $_POST['capacidade_maxima'] ?? 0,
            'status' => $_POST['status'] ?? 'ativo',
            'observacoes' => $_POST['observacoes'] ?? ''
        ];
        
        if (empty($data['codigo'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Código é obrigatório'
            ]);
            return;
        }
        
        if (empty($data['descricao'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Descrição é obrigatória'
            ]);
            return;
        }
        
        if (empty($data['tipo'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Tipo é obrigatório'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        
        if ($armazenagem->updateArmazenagem($armazenagemId, $data)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Armazenagem atualizada com sucesso',
                'redirect' => DOMAIN . '/armazenagens'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao atualizar armazenagem'
            ]);
        }
    }

    public function delete(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'excluir')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para excluir armazenagens'
            ]);
            return;
        }
        
        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da armazenagem não informado'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        if ($armazenagem->deleteArmazenagem($armazenagemId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Armazenagem excluída com sucesso'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao excluir armazenagem. Verifique se não há itens alocados.'
            ]);
        }
    }

    public function show(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            echo 'Sem permissão para visualizar armazenagens.';
            return;
        }
        
        $armazenagemId = $params['id'] ?? null;
        if (!$armazenagemId) {
            echo 'ID da armazenagem não informado.';
            return;
        }
        
        $armazenagem = new Armazenagem();
        $armazenagemData = $armazenagem->getArmazenagemById($armazenagemId);
        if (!$armazenagemData->getResult()) {
            echo 'Armazenagem não encontrada.';
            return;
        }
        
        $armazenagemInfo = $armazenagemData->getResult()[0];
        
        // Buscar produtos armazenados
        $produtos = $armazenagem->getProdutosArmazenados($armazenagemId);
        
        // Buscar estatísticas
        $estatisticas = $armazenagem->getEstatisticasArmazenagem($armazenagemId);
        
        $this->render('pages/armazenagens/show.twig', [
            'armazenagem' => $armazenagemInfo,
            'produtos' => $produtos->getResult() ?? [],
            'total_quantidade' => $estatisticas['total_quantidade'] ?? 0,
            'movimentacoes_entrada' => $estatisticas['movimentacoes_entrada'] ?? 0,
            'movimentacoes_saida' => $estatisticas['movimentacoes_saida'] ?? 0
        ]);
    }

    public function mapa(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('armazenagens', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $armazenagem = new Armazenagem();
        $mapa = $armazenagem->getMapaArmazenagens();
        
        $this->render('pages/armazenagens/mapa.twig', [
            'mapa' => $mapa->getResult() ?? []
        ]);
    }

    public function buscarPorCodigo(array $params)
    {
        $this->setParams($params);
        $codigo = $_GET['codigo'] ?? '';
        
        if (empty($codigo)) {
            $this->responseJson([
                'success' => false,
                'error' => 'Código é obrigatório'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        $resultado = $armazenagem->getArmazenagemByCodigo($codigo);
        
        if ($resultado->getResult()) {
            $this->responseJson([
                'success' => true,
                'armazenagem' => $resultado->getResult()[0]
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Armazenagem não encontrada'
            ]);
        }
    }

    public function buscarPorTipo(array $params)
    {
        $this->setParams($params);
        $tipo = $_GET['tipo'] ?? '';
        
        if (empty($tipo)) {
            $this->responseJson([
                'success' => false,
                'error' => 'Tipo é obrigatório'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        $resultado = $armazenagem->getArmazenagensByTipo($tipo);
        
        $this->responseJson([
            'success' => true,
            'armazenagens' => $resultado->getResult() ?? []
        ]);
    }

    public function buscarPorSetor(array $params)
    {
        $this->setParams($params);
        $setor = $_GET['setor'] ?? '';
        
        if (empty($setor)) {
            $this->responseJson([
                'success' => false,
                'error' => 'Setor é obrigatório'
            ]);
            return;
        }
        
        $armazenagem = new Armazenagem();
        $resultado = $armazenagem->getArmazenagensBySetor($setor);
        
        $this->responseJson([
            'success' => true,
            'armazenagens' => $resultado->getResult() ?? []
        ]);
    }
}