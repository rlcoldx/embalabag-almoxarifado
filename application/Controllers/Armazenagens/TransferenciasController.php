<?php

namespace Agencia\Close\Controllers\Armazenagens;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Models\ItemNF\ItemNF;

class TransferenciasController extends Controller
{
    public function index($params)
    {
        $this->setParams($params);
        
        $transferencias = new \Agencia\Close\Models\Transferencias\Transferencias();
        $lista_transferencias = $transferencias->getTransferencias()->getResult();

        $this->render('pages/armazenagens/transferencias/index.twig', [
            'menu' => 'armazenagens',
            'transferencias' => $lista_transferencias
        ]);
    }

    public function create($params)
    {
        $this->setParams($params);
        
        $armazenagens = new Armazenagem();
        $lista_armazenagens = $armazenagens->getArmazenagens()->getResult();

        $itens = new ItemNF();
        $lista_itens = $itens->getItensDisponiveis()->getResult();

        $this->render('pages/armazenagens/transferencias/create.twig', [
            'menu' => 'armazenagens',
            'armazenagens' => $lista_armazenagens,
            'itens' => $lista_itens
        ]);
    }

    public function store($params)
    {
        $this->setParams($params);
        
        $transferencias = new \Agencia\Close\Models\Transferencias\Transferencias();
        $result = $transferencias->createTransferencia($this->params);

        if ($result) {
            header("Content-Type: application/json");
            echo json_encode([
                'success' => true,
                'message' => 'Transferência solicitada com sucesso'
            ]);
        } else {
            header("Content-Type: application/json");
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao solicitar transferência'
            ]);
        }
    }

    public function execute($params)
    {
        $this->setParams($params);
        
        $transferencias = new \Agencia\Close\Models\Transferencias\Transferencias();
        $result = $transferencias->executarTransferencia($this->params['id']);

        if ($result) {
            header("Content-Type: application/json");
            echo json_encode([
                'success' => true,
                'message' => 'Transferência executada com sucesso'
            ]);
        } else {
            header("Content-Type: application/json");
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao executar transferência'
            ]);
        }
    }

    public function cancel($params)
    {
        $this->setParams($params);
        
        $transferencias = new \Agencia\Close\Models\Transferencias\Transferencias();
        $result = $transferencias->cancelarTransferencia($this->params['id']);

        if ($result) {
            header("Content-Type: application/json");
            echo json_encode([
                'success' => true,
                'message' => 'Transferência cancelada com sucesso'
            ]);
        } else {
            header("Content-Type: application/json");
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao cancelar transferência'
            ]);
        }
    }

    public function view($params)
    {
        $this->setParams($params);
        
        $transferencias = new \Agencia\Close\Models\Transferencias\Transferencias();
        $transferencia = $transferencias->getTransferencia($this->params['id'])->getResult();

        if ($transferencia) {
            $this->render('pages/armazenagens/transferencias/view.twig', [
                'menu' => 'armazenagens',
                'transferencia' => $transferencia[0]
            ]);
        } else {
            echo "Transferência não encontrada";
        }
    }
} 