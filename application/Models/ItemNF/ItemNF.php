<?php

namespace Agencia\Close\Models\ItemNF;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class ItemNF extends Model
{
    public function getItensByNotaFiscal(int $notaFiscalId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT inf.*, p.descricao as produto_descricao, p.codigo as produto_codigo, p.codigo_barras
            FROM itens_nf inf
            LEFT JOIN produtos p ON inf.produto_id = p.id
            WHERE inf.nota_fiscal_id = :nota_fiscal_id
            ORDER BY inf.id
        ", "nota_fiscal_id={$notaFiscalId}");
        return $this->read;
    }

    public function getItemById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT inf.*, p.descricao as produto_descricao, p.codigo as produto_codigo
            FROM itens_nf inf
            LEFT JOIN produtos p ON inf.produto_id = p.id
            WHERE inf.id = :id
        ", "id={$id}");
        return $this->read;
    }

    public function getItensPendentesConferencia(int $notaFiscalId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT inf.*, p.descricao as produto_descricao, p.codigo as produto_codigo
            FROM itens_nf inf
            LEFT JOIN produtos p ON inf.produto_id = p.id
            WHERE inf.nota_fiscal_id = :nota_fiscal_id AND inf.status = 'pendente'
            ORDER BY inf.id
        ", "nota_fiscal_id={$notaFiscalId}");
        return $this->read;
    }

    public function getItensAlocados(int $notaFiscalId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT inf.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   a.codigo as armazenagem_codigo, a.descricao as armazenagem_descricao
            FROM itens_nf inf
            LEFT JOIN produtos p ON inf.produto_id = p.id
            LEFT JOIN armazenagens a ON inf.armazenagem_id = a.id
            WHERE inf.nota_fiscal_id = :nota_fiscal_id AND inf.status = 'alocado'
            ORDER BY inf.id
        ", "nota_fiscal_id={$notaFiscalId}");
        return $this->read;
    }

    public function createItemNF(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("itens_nf", $data);
        return $this->create->getResult();
    }

    public function updateItemNF(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("itens_nf", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteItemNF(int $id): bool
    {
        $this->delete = new Delete();
        $this->delete->ExeDelete("itens_nf", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function marcarComoConferido(int $id, int $quantidadeConferida): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("itens_nf", 
            [
                'status' => 'conferido',
                'quantidade_conferida' => $quantidadeConferida,
                'data_conferencia' => date('Y-m-d H:i:s')
            ], 
            "WHERE id = :id", 
            "id={$id}"
        );
        $result = $this->update->getResult();
        return $result === true;
    }

    public function alocarItem(int $id, int $armazenagemId, int $quantidade): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("itens_nf", 
            [
                'status' => 'alocado',
                'armazenagem_id' => $armazenagemId,
                'quantidade_alocada' => $quantidade,
                'data_alocacao' => date('Y-m-d H:i:s')
            ], 
            "WHERE id = :id", 
            "id={$id}"
        );
        $result = $this->update->getResult();
        return $result === true;
    }

    public function transferirItem(int $id, int $novaArmazenagemId): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("itens_nf", 
            [
                'armazenagem_id' => $novaArmazenagemId,
                'data_transferencia' => date('Y-m-d H:i:s')
            ], 
            "WHERE id = :id", 
            "id={$id}"
        );
        $result = $this->update->getResult();
        return $result === true;
    }

    public function getItensPorProduto(int $produtoId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT inf.*, nf.numero as nf_numero, nf.serie as nf_serie, nf.fornecedor
            FROM itens_nf inf
            LEFT JOIN notas_fiscais nf ON inf.nota_fiscal_id = nf.id
            WHERE inf.produto_id = :produto_id
            ORDER BY nf.data_emissao DESC
        ", "produto_id={$produtoId}");
        return $this->read;
    }

    public function getItensPorArmazenagem(int $armazenagemId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT inf.*, p.descricao as produto_descricao, p.codigo as produto_codigo,
                   nf.numero as nf_numero, nf.serie as nf_serie
            FROM itens_nf inf
            LEFT JOIN produtos p ON inf.produto_id = p.id
            LEFT JOIN notas_fiscais nf ON inf.nota_fiscal_id = nf.id
            WHERE inf.armazenagem_id = :armazenagem_id AND inf.status = 'alocado'
            ORDER BY p.descricao
        ", "armazenagem_id={$armazenagemId}");
        return $this->read;
    }
}