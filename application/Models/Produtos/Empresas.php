<?php

namespace Agencia\Close\Models\Produtos;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Empresas extends Model
{

    public function getEmpresas(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM usuarios WHERE tipo = '2' ORDER BY id DESC");
        return $read;
    }

    public function getEmpresaID($id): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM usuarios WHERE id = :id", "id={$id}");
        return $read;
    }

    public function saveEmpresa(array $params): Create
    {
        $create = new Create();
        if($params['senha'] != '') {
            $params['senha'] = sha1($params['senha']);
        }else{
            unset($params['senha']);
        }
        unset($params['resenha']);
        $params['tipo'] = 2;
        $create->ExeCreate('usuarios', $params);
        return $create;
    }

    public function saveEmpresaEditar(array $params): Update
    {
        $update = new Update();
        
        if($params['senha'] != '') {
            $params['senha'] = sha1($params['senha']);
        }else{
            unset($params['senha']);
        }
        unset($params['resenha']);

        $id = $params['id'];
        unset($params['id']);

        $update->ExeUpdate('usuarios', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }

    public function saveEmpresaStatus(array $params): Update
    {
        $update = new Update();
        $id = $params['id'];
        unset($params['id']);
        $update->ExeUpdate('usuarios', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }

}