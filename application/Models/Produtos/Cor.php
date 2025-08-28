<?php

namespace Agencia\Close\Models\Produtos;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class Cor extends Model
{
    public function getCorList(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cores ORDER BY id DESC");
        return $this->read;
    }

    public function getCores(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cores ORDER BY cor_nome ASC");
        return $this->read;
    }

    public function getCorID($id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cores WHERE id = :id", "id={$id}");
        return $this->read;
    }

    public function getCorName($cor_nome): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cores WHERE cor_nome = :cor_nome", "cor_nome={$cor_nome}");
        return $this->read;
    }


    public function createCor($params): Create
    {
        $create = new Create();
        unset($params['action']);
        unset($params['cor_old']);

        $create->ExeCreate('cores', $params);
        return $create;
    }


    public function editarCor($params): Update
    {
        $update = new Update();
        $id = $params['id'];
        unset($params['id']);
        unset($params['action']);

        if ($params['cor_old'] != '') {
            $read = new Read();
            $read->FullRead("UPDATE `produtos_variations` SET `cor` = :cor_nome WHERE `cor` = :cor_old", "cor_nome={$params['cor_nome']}&cor_old={$params['cor_old']}");

            $read = new Read();
            $read->FullRead("UPDATE `pedidos_itens` SET `variavel` = :cor_nome WHERE `variavel` = :cor_old", "cor_nome={$params['cor_nome']}&cor_old={$params['cor_old']}");
        }

        unset($params['cor_old']);
        $update->ExeUpdate('cores', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }

    public function removerCor($params): Read
    {
        $read = new Read();
        $read->FullRead("DELETE FROM `cores` WHERE `id` = :id", "id={$params['id']}");

        $read = new Read();
        $read->FullRead("DELETE FROM `produtos_variations` WHERE `cor` = :cor_nome", "cor_nome={$params['cor_nome']}");

        return $read;
    }
}