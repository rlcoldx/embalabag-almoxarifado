<?php

namespace Agencia\Close\Controllers\Expedicao;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Models\Expedicao\Romaneio;

class CiaAereaController extends Controller
{
    public function index(array $params)
    {
        if (!$this->podeAcessar($params)) {
            return;
        }

        $companhiaId = $this->companhiaId();
        $romaneio = new Romaneio();

        $this->render('pages/cia-aerea/index.twig', [
            'menu' => 'cia_aerea',
            'romaneios' => $romaneio->listar($companhiaId),
            'somente_companhia' => ($_SESSION[BASE . 'user_tipo'] ?? '') === '3',
        ]);
    }

    public function show(array $params)
    {
        if (!$this->podeAcessar($params)) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $romaneio = new Romaneio();
        $dados = $romaneio->getById($id);
        if (!$dados) {
            echo 'Romaneio não encontrado.';
            return;
        }

        if (($_SESSION[BASE . 'user_tipo'] ?? '') === '3' && (int)$dados['companhia_id'] !== (int)$_SESSION[BASE . 'user_id']) {
            echo 'Este romaneio não pertence à sua companhia.';
            return;
        }

        $this->render('pages/expedicao/romaneio-show.twig', [
            'menu' => 'cia_aerea',
            'romaneio' => $dados,
            'itens' => $romaneio->getItens($id),
            'imprimir' => false,
            'portal_cia' => true,
        ]);
    }

    public function receber(array $params)
    {
        if (!$this->podeAcessar($params)) {
            return;
        }

        $tipo = $_SESSION[BASE . 'user_tipo'] ?? '';
        $helper = new PermissionHelper();
        if ($tipo !== '1' && $tipo !== '3' && !$helper->userHasPermission('cia_aerea', 'receber')) {
            echo 'Sem permissão para confirmar recebimento.';
            return;
        }

        $id = (int)($params['id'] ?? 0);
        (new Romaneio())->marcarRecebido($id);
        header('Location: ' . DOMAIN . '/cia-aerea/romaneios/' . $id);
        exit;
    }

    private function podeAcessar(array $params): bool
    {
        $this->checkSession();
        $this->setParams($params);
        $tipo = $_SESSION[BASE . 'user_tipo'] ?? '';
        $helper = new PermissionHelper();
        if ($tipo === '1' || $tipo === '3' || $helper->userHasPermission('cia_aerea', 'visualizar')) {
            return true;
        }
        echo 'Sem permissão para o portal da cia aérea.';
        return false;
    }

    private function companhiaId(): ?int
    {
        return ($_SESSION[BASE . 'user_tipo'] ?? '') === '3'
            ? (int)$_SESSION[BASE . 'user_id']
            : null;
    }
}
