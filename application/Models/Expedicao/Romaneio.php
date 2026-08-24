<?php

namespace Agencia\Close\Models\Expedicao;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Romaneio extends Model
{
    public function listar(?int $companhiaId = null): array
    {
        $read = new Read();
        $sql = "
            SELECT r.*, u.nome as usuario_nome, c.nome as companhia_nome, c.companhia as companhia_empresa,
                   (SELECT COUNT(*) FROM romaneio_itens ri WHERE ri.romaneio_id = r.id) as total_pedidos
            FROM romaneios r
            LEFT JOIN usuarios u ON u.id = r.usuario_id
            LEFT JOIN usuarios c ON c.id = r.companhia_id
        ";
        if ($companhiaId) {
            $read->FullRead($sql . " WHERE r.companhia_id = :cid ORDER BY r.id DESC", "cid={$companhiaId}");
        } else {
            $read->FullRead($sql . " ORDER BY r.id DESC");
        }
        return $read->getResult() ?: [];
    }

    public function getById(int $id): ?array
    {
        $read = new Read();
        $read->FullRead("
            SELECT r.*, u.nome as usuario_nome, c.nome as companhia_nome, c.companhia as companhia_empresa
            FROM romaneios r
            LEFT JOIN usuarios u ON u.id = r.usuario_id
            LEFT JOIN usuarios c ON c.id = r.companhia_id
            WHERE r.id = :id
        ", "id={$id}");
        return $read->getResult()[0] ?? null;
    }

    public function getItens(int $romaneioId): array
    {
        $read = new Read();
        $read->FullRead("
            SELECT ri.*, p.codigo, p.nome_cliente, p.base_destino, p.voo_1, p.codigoSige,
                   p.etiqueta_interna, p.etiqueta_cia_aerea, p.status_expedicao
            FROM romaneio_itens ri
            INNER JOIN pedidos p ON p.id = ri.pedido_id
            WHERE ri.romaneio_id = :id
        ", "id={$romaneioId}");
        return $read->getResult() ?: [];
    }

    public function criar(array $data, array $pedidoIds): int|false
    {
        $create = new Create();
        $create->ExeCreate('romaneios', $data);
        $id = $create->getResult();
        if (!$id) {
            return false;
        }

        $itemCreate = new Create();
        $expedicao = new Expedicao();
        foreach ($pedidoIds as $pedidoId) {
            $pedidoId = (int)$pedidoId;
            if ($pedidoId <= 0) {
                continue;
            }
            $itemCreate->ExeCreate('romaneio_itens', [
                'romaneio_id' => (int)$id,
                'pedido_id' => $pedidoId,
            ]);
            $expedicao->atualizarStatus($pedidoId, 'romaneio');
        }

        return (int)$id;
    }

    public function marcarEnviado(int $id): bool
    {
        $update = new Update();
        $update->ExeUpdate('romaneios', [
            'status' => 'enviado',
            'data_envio' => date('Y-m-d H:i:s'),
        ], 'WHERE id = :id', "id={$id}");

        $itens = $this->getItens($id);
        $expedicao = new Expedicao();
        foreach ($itens as $item) {
            $expedicao->atualizarStatus((int)$item['pedido_id'], 'enviado');
        }
        return true;
    }

    public function marcarRecebido(int $id): bool
    {
        $update = new Update();
        $update->ExeUpdate('romaneios', [
            'status' => 'recebido',
            'data_recebimento' => date('Y-m-d H:i:s'),
        ], 'WHERE id = :id', "id={$id}");
        return true;
    }

    public function proximoCodigo(): string
    {
        return 'ROM-' . date('Ymd') . '-' . substr((string)time(), -4);
    }
}
