<?php

namespace Agencia\Close\Controllers\Armazenagens;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Models\Transferencias\Transferencias;

class TransferenciasController extends Controller
{
    public function index($params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->temPermissao('visualizar')) {
            echo 'Sem permissão para acessar transferências.';
            return;
        }

        $this->render('pages/armazenagens/transferencias/index.twig', [
            'menu' => 'armazenagens_transferencias'
        ]);
    }

    public function create($params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->temPermissao('criar')) {
            echo 'Sem permissão para criar transferências.';
            return;
        }

        $armazenagens = new Armazenagem();
        $transferencias = new Transferencias();

        $this->render('pages/armazenagens/transferencias/create.twig', [
            'menu' => 'armazenagens_transferencias',
            'armazenagens' => $armazenagens->getArmazenagensAtivas()->getResult() ?: [],
            'itens' => $transferencias->getItensDisponiveis()->getResult() ?: [],
        ]);
    }

    public function store($params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->temPermissao('criar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para criar transferências.'
            ]);
            return;
        }

        $transferencias = new Transferencias();
        $id = $transferencias->createTransferencia($_POST);

        if ($id) {
            $this->responseJson([
                'success' => true,
                'message' => 'Transferência solicitada com sucesso.',
                'redirect' => DOMAIN . '/transferencias/view/' . $id
            ]);
            return;
        }

        $this->responseJson([
            'success' => false,
            'message' => 'Erro ao solicitar transferência. Verifique item, origem, destino e quantidade.'
        ]);
    }

    public function execute($params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->temPermissao('executar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para executar transferências.'
            ]);
            return;
        }

        $id = $params['id'] ?? null;
        $transferencias = new Transferencias();
        $result = $id && $transferencias->executarTransferencia($id);

        if ($result) {
            $this->responseJson([
                'success' => true,
                'message' => 'Transferência executada com sucesso.',
                'redirect' => DOMAIN . '/transferencias/view/' . $id
            ]);
            return;
        }

        $this->responseJson([
            'success' => false,
            'message' => 'Erro ao executar transferência. Verifique se ela está pendente e se há espaço no destino.'
        ]);
    }

    public function cancel($params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->temPermissao('executar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para cancelar transferências.'
            ]);
            return;
        }

        $id = $params['id'] ?? null;
        $transferencias = new Transferencias();
        $result = $id && $transferencias->cancelarTransferencia($id);

        if ($result) {
            $this->responseJson([
                'success' => true,
                'message' => 'Transferência cancelada com sucesso.'
            ]);
            return;
        }

        $this->responseJson([
            'success' => false,
            'message' => 'Erro ao cancelar transferência. Apenas solicitações pendentes podem ser canceladas.'
        ]);
    }

    public function view($params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->temPermissao('visualizar')) {
            echo 'Sem permissão para visualizar transferências.';
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id) {
            echo 'Transferência não encontrada.';
            return;
        }

        $transferencias = new Transferencias();
        $result = $transferencias->getTransferencia($id)->getResult();

        if (!$result) {
            echo 'Transferência não encontrada.';
            return;
        }

        $this->render('pages/armazenagens/transferencias/view.twig', [
            'menu' => 'armazenagens_transferencias',
            'transferencia' => $result[0]
        ]);
    }

    private function temPermissao(string $acao): bool
    {
        $permissionHelper = new PermissionHelper();
        return $permissionHelper->userHasPermission('movimentacoes', $acao)
            || $permissionHelper->userHasPermission('armazenagens', $acao === 'visualizar' ? 'visualizar' : 'transferir');
    }
}
