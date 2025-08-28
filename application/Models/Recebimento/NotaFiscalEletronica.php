<?php

namespace Agencia\Close\Models\Recebimento;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class NotaFiscalEletronica extends Model
{
    private string $table = 'notas_fiscais_eletronicas';
    private string $tableItens = 'itens_nfe';

    /**
     * Buscar NF-e por número
     */
    public function getByNumero(string $numero): Read
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, 'WHERE numero_nfe = :numero', "numero={$numero}");
        return $this->read;
    }

    /**
     * Buscar NF-e por chave de acesso
     */
    public function getByChaveAcesso(string $chave): Read
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, 'WHERE chave_acesso = :chave', "chave={$chave}");
        return $this->read;
    }

    /**
     * Buscar NF-e por pedido
     */
    public function getByPedido(int $pedidoId): Read
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, 'WHERE pedido_id = :pedido_id', "pedido_id={$pedidoId}");
        return $this->read;
    }

    /**
     * Listar todas as NF-e com informações do fornecedor e pedido
     */
    public function getAllWithDetails(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nfe.*, 
                   f.nome as fornecedor_nome,
                   f.email as fornecedor_email,
                   p.numero_pedido,
                   u.nome as usuario_nome
            FROM {$this->table} nfe
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            LEFT JOIN pedidos p ON nfe.pedido_id = p.id
            LEFT JOIN usuarios u ON nfe.usuario_recebimento_id = u.id
            ORDER BY nfe.data_recebimento DESC
        ");
        return $this->read;
    }

    /**
     * Criar nova NF-e
     */
    public function createNFe(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate($this->table, $data);
        return $this->create->getResult();
    }

    /**
     * Atualizar NF-e
     */
    public function updateNFe(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate($this->table, $data, "WHERE id = :id", "id={$id}");
        return $this->update->getResult() === true;
    }

    /**
     * Excluir NF-e
     */
    public function deleteNFe(int $id): bool
    {
        $this->delete = new Delete();
        $this->delete->ExeDelete($this->table, "WHERE id = :id", "id={$id}");
        return $this->delete->getResult() === true;
    }

    /**
     * Adicionar item à NF-e
     */
    public function addItem(array $itemData): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate($this->tableItens, $itemData);
        return $this->create->getResult();
    }

    /**
     * Buscar itens de uma NF-e
     */
    public function getItens(int $nfeId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT i.*, 
                   p.nome as produto_nome,
                   p.sku as produto_sku,
                   v.tamanho,
                   v.cor,
                   v.estoque_atual
            FROM {$this->tableItens} i
            INNER JOIN produtos p ON i.produto_id = p.id
            INNER JOIN produtos_variations v ON i.variacao_id = v.id
            WHERE i.nfe_id = :nfe_id
            ORDER BY p.nome, v.tamanho, v.cor
        ", "nfe_id={$nfeId}");
        return $this->read;
    }

    /**
     * Verificar se NF-e já existe
     */
    public function nfeExists(string $numero): bool
    {
        $result = $this->getByNumero($numero);
        return $result->getResult() !== null;
    }

    /**
     * Verificar se chave de acesso já existe
     */
    public function chaveExists(string $chave): bool
    {
        $result = $this->getByChaveAcesso($chave);
        return $result->getResult() !== null;
    }

    /**
     * Atualizar status da NF-e
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->updateNFe($id, ['status' => $status]);
    }

    /**
     * Buscar NF-e pendentes de conferência
     */
    public function getPendentesConferencia(): Read
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, 'WHERE status = :status', "status=pendente");
        return $this->read;
    }
}
