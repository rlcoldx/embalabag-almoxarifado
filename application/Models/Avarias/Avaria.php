<?php

namespace Agencia\Close\Models\Avarias;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Avaria extends Model
{
    public function listar(): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT a.*, p.nome as produto_nome, p.SKU as produto_sku,
                   ar.codigo as endereco_codigo,
                   ur.nome as usuario_reportou_nome,
                   us.nome as usuario_responsavel_nome
            FROM avarias a
            LEFT JOIN produtos p ON p.id = a.produto_id
            LEFT JOIN armazenagens ar ON ar.id = a.armazenagem_id
            LEFT JOIN usuarios ur ON ur.id = a.usuario_reportou
            LEFT JOIN usuarios us ON us.id = a.usuario_responsavel
            ORDER BY a.data_ocorrencia DESC
        ");
        return $read->getResult() ?: [];
    }

    public function criar(array $data): int|false
    {
        $create = new Create();
        $create->ExeCreate('avarias', $data);
        $id = $create->getResult();
        return $id ? (int)$id : false;
    }

    public function atualizar(int $id, array $data): bool
    {
        $update = new Update();
        $update->ExeUpdate('avarias', $data, 'WHERE id = :id', "id={$id}");
        return true;
    }
}
