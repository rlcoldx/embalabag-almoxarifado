<?php

namespace Agencia\Close\Controllers\Produtos;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Produtos\Cor;

class CorController extends Controller
{
    public function index($params)
    {
        $this->setParams($params);

        $cor = new Cor();
        $cores_lista = $cor->getCorList()->getResult();

        $this->render('pages/produtos/cores.twig', ['menu' => 'produtos', 'cores' => $cores_lista]);
    }


    public function editar($params)
    {
        $this->setParams($params);

        $editar = new Cor();
        $editar = $editar->getCorID($params['id'])->getResult()[0];

        $cor = new Cor();
        $cores_lista = $cor->getCorList()->getResult();

        $this->render('pages/produtos/cores.twig', ['menu' => 'produtos', 'cores' => $cores_lista, 'editar' => $editar]);
    }


    public function save($params)
    {
        // Para dados JSON, precisamos ler do input stream
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input) {
            $this->setParams($input);
        } else {
            $this->setParams($params);
        }

        $createCor = new Cor();
        $createCor = $createCor->createCor($this->params)->getResult();
        if ($createCor) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    public function save_edit($params)
    {
        // Para dados JSON, precisamos ler do input stream
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input) {
            $this->setParams($input);
        } else {
            $this->setParams($params);
        }

        $editarCor = new Cor();
        $editarCor = $editarCor->editarCor($this->params)->getResult();

        if ($editarCor) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    public function remove_color($params)
    {
        $this->setParams($params);

        $removerCor = new Cor();
        $removerCor = $removerCor->removerCor($this->params)->getResult();

        if ($removerCor) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
}