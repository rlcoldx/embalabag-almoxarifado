<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\ItemNF\ItemNF;
use Agencia\Close\Models\Produtos\Produtos;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Models\Movimentacao\Movimentacao;
use Agencia\Close\Helpers\User\PermissionHelper;

class RecebimentoController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession(); // Verificar sessão antes de renderizar
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $notasFiscais = $notaFiscal->getAllNotasFiscais();
        
        $this->render('pages/recebimento/index.twig', [
            'menu' => 'recebimento',
            'notasFiscais' => $notasFiscais->getResult() ?? []
        ]);
    }

    public function create(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'criar')) {
            echo 'Sem permissão para criar notas fiscais.';
            return;
        }
        
        $this->render('pages/recebimento/create.twig', [
            'menu' => 'recebimento'
        ]);
    }

    public function store(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'criar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para criar notas fiscais'
            ]);
            return;
        }
        
        $data = [
            'numero' => $_POST['numero'] ?? '',
            'serie' => $_POST['serie'] ?? '',
            'fornecedor' => $_POST['fornecedor'] ?? '',
            'data_emissao' => $_POST['data_emissao'] ?? '',
            'valor_total' => $_POST['valor_total'] ?? 0,
            'observacoes' => $_POST['observacoes'] ?? '',
            'status' => 'pendente'
        ];
        
        if (empty($data['numero'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Número da NF é obrigatório'
            ]);
            return;
        }
        
        if (empty($data['fornecedor'])) {
            $this->responseJson([
                'success' => false,
                'error' => 'Fornecedor é obrigatório'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        
        // Verifica se NF já existe
        $nfExistente = $notaFiscal->getNotaFiscalByNumero($data['numero'], $data['serie']);
        if ($nfExistente->getResult()) {
            $this->responseJson([
                'success' => false,
                'error' => 'Nota Fiscal já existe'
            ]);
            return;
        }
        
        $nfId = $notaFiscal->createNotaFiscal($data);
        if ($nfId) {
            $this->responseJson([
                'success' => true,
                'message' => 'Nota Fiscal criada com sucesso',
                'redirect' => DOMAIN . '/recebimento'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao criar Nota Fiscal'
            ]);
        }
    }

    public function view(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            echo 'Sem permissão para visualizar notas fiscais.';
            return;
        }
        
        $nfId = $params['id'] ?? null;
        if (!$nfId) {
            echo 'ID da Nota Fiscal não informado.';
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $nfData = $notaFiscal->getNotaFiscalById($nfId);
        if (!$nfData->getResult()) {
            echo 'Nota Fiscal não encontrada.';
            return;
        }
        
        $this->render('pages/recebimento/view.twig', [
            'menu' => 'recebimento',
            'notaFiscal' => $nfData->getResult()
        ]);
    }

    public function marcarComoRecebida(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'editar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para marcar como recebida'
            ]);
            return;
        }
        
        $nfId = $params['id'] ?? null;
        if (!$nfId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da Nota Fiscal não informado'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $usuarioId = $_SESSION[BASE . 'user_id'] ?? 0;
        if ($notaFiscal->marcarComoRecebida($nfId, $usuarioId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Nota Fiscal marcada como recebida'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao marcar como recebida'
            ]);
        }
    }

    public function conferencia(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'conferir')) {
            echo 'Sem permissão para conferir itens.';
            return;
        }
        
        $nfId = $params['id'] ?? null;
        if (!$nfId) {
            echo 'ID da Nota Fiscal não informado.';
            return;
        }
        
        $itemNF = new ItemNF();
        $itensPendentes = $itemNF->getItensPendentesConferencia($nfId);
        
        $armazenagem = new Armazenagem();
        $armazenagens = $armazenagem->getArmazenagensAtivas();
        
        $this->render('pages/recebimento/conferencia.twig', [
            'menu' => 'recebimento',
            'nfId' => $nfId,
            'itensPendentes' => $itensPendentes->getResult() ?? [],
            'armazenagens' => $armazenagens->getResult() ?? []
        ]);
    }

    public function conferirItem(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'conferir')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para conferir itens'
            ]);
            return;
        }
        
        $itemId = $_POST['item_id'] ?? null;
        $quantidadeConferida = $_POST['quantidade_conferida'] ?? 0;
        
        if (!$itemId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do item não informado'
            ]);
            return;
        }
        
        if ($quantidadeConferida <= 0) {
            $this->responseJson([
                'success' => false,
                'error' => 'Quantidade conferida deve ser maior que zero'
            ]);
            return;
        }
        
        $itemNF = new ItemNF();
        if ($itemNF->marcarComoConferido($itemId, $quantidadeConferida)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Item conferido com sucesso'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao conferir item'
            ]);
        }
    }

    public function alocarItem(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'alocar')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para alocar itens'
            ]);
            return;
        }
        
        $itemId = $_POST['item_id'] ?? null;
        $armazenagemId = $_POST['armazenagem_id'] ?? null;
        $quantidade = $_POST['quantidade'] ?? 0;
        
        if (!$itemId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do item não informado'
            ]);
            return;
        }
        
        if (!$armazenagemId) {
            $this->responseJson([
                'success' => false,
                'error' => 'Armazenagem não informada'
            ]);
            return;
        }
        
        if ($quantidade <= 0) {
            $this->responseJson([
                'success' => false,
                'error' => 'Quantidade deve ser maior que zero'
            ]);
            return;
        }
        
        $itemNF = new ItemNF();
        if ($itemNF->alocarItem($itemId, $armazenagemId, $quantidade)) {
            // Registrar movimentação
            $itemData = $itemNF->getItemById($itemId);
            $resultado = $itemData->getResult();
            if ($resultado) {
                $movimentacao = new Movimentacao();
                $movimentacao->registrarEntrada(
                    $resultado[0]['produto_id'],
                    $armazenagemId,
                    $quantidade,
                    $_SESSION[BASE.'user_id'],
                    'Alocação por NF'
                );
            }
            
            $this->responseJson([
                'success' => true,
                'message' => 'Item alocado com sucesso'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao alocar item'
            ]);
        }
    }

    public function finalizarConferencia(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'conferir')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para finalizar conferência'
            ]);
            return;
        }
        
        $nfId = $params['id'] ?? null;
        if (!$nfId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID da Nota Fiscal não informado'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $usuarioId = $_SESSION[BASE . 'user_id'] ?? 0;
        if ($notaFiscal->marcarComoConferida($nfId, $usuarioId)) {
            $this->responseJson([
                'success' => true,
                'message' => 'Conferência finalizada com sucesso',
                'redirect' => DOMAIN . '/recebimento'
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Erro ao finalizar conferência'
            ]);
        }
    }

    public function buscarProduto(array $params)
    {
        $this->setParams($params);
        $codigo = $_GET['codigo'] ?? '';
        $codigoBarras = $_GET['codigo_barras'] ?? '';
        
        if (empty($codigo) && empty($codigoBarras)) {
            $this->responseJson([
                'success' => false,
                'error' => 'Código ou código de barras é obrigatório'
            ]);
            return;
        }
        
        $produto = new Produtos();
        $resultado = null;
        
        if (!empty($codigo)) {
            $resultado = $produto->getProdutoByCodigo($codigo);
        } else {
            $resultado = $produto->getProdutoByCodigoBarras($codigoBarras);
        }
        
        if ($resultado->getResult()) {
            $this->responseJson([
                'success' => true,
                'produto' => $resultado->getResult()[0]
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Produto não encontrado'
            ]);
        }
    }

    public function gerarEtiqueta(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'gerar_etiqueta')) {
            $this->responseJson([
                'success' => false,
                'error' => 'Sem permissão para gerar etiquetas'
            ]);
            return;
        }
        
        $itemId = $_POST['item_id'] ?? null;
        if (!$itemId) {
            $this->responseJson([
                'success' => false,
                'error' => 'ID do item não informado'
            ]);
            return;
        }
        
        $itemNF = new ItemNF();
        $itemData = $itemNF->getItemById($itemId);
        $resultado = $itemData->getResult();
        
        if ($resultado) {
            // Aqui você implementaria a geração da etiqueta
            // Por enquanto, apenas retorna sucesso
            $this->responseJson([
                'success' => true,
                'message' => 'Etiqueta gerada com sucesso',
                'dados_etiqueta' => $resultado[0]
            ]);
        } else {
            $this->responseJson([
                'success' => false,
                'error' => 'Item não encontrado'
            ]);
        }
    }
}