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
     * Buscar NF-e por ID com dados do fornecedor
     */
    public function getById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT nfe.*,
                   f.nome as fornecedor_nome,
                   f.email as fornecedor_email,
                   p.codigo as pedido_codigo,
                   p.codigo as numero_pedido,
                   u.nome as usuario_nome
            FROM {$this->table} nfe
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            LEFT JOIN pedidos p ON nfe.pedido_id = p.id
            LEFT JOIN usuarios u ON nfe.usuario_recebimento_id = u.id
            WHERE nfe.id = :id
            LIMIT 1
        ", "id={$id}");
        return $this->read;
    }

    /**
     * Buscar NF-e por ID com dados do fornecedor (alias legado)
     */
    public function getByIdWithDetails(int $id): Read
    {
        return $this->getById($id);
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
                   p.codigo as pedido_codigo,
                   p.codigo as numero_pedido,
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
        return $this->getItensParaConferencia($nfeId);
    }

    /**
     * Itens da NF-e formatados para a tela/modal de conferência
     */
    public function getItensParaConferencia(int $nfeId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT i.id as item_nfe_id,
                   i.nfe_id,
                   i.produto_id,
                   i.variacao_id,
                   i.quantidade,
                   i.valor_unitario,
                   i.valor_total,
                   p.nome as nome_produto,
                   p.SKU as sku,
                   p.categoria,
                   v.tamanho,
                   c.nome as cor,
                   v.estoque as estoque_atual
            FROM {$this->tableItens} i
            INNER JOIN produtos p ON i.produto_id = p.id
            INNER JOIN produtos_variations v ON i.variacao_id = v.id
            LEFT JOIN cores c ON v.cor = c.id
            WHERE i.nfe_id = :nfe_id
            ORDER BY p.nome, v.tamanho, c.nome
        ", "nfe_id={$nfeId}");
        return $this->read;
    }

    /**
     * Buscar item da NF-e por SKU do produto
     */
    public function getItemBySkuForNfe(int $nfeId, string $sku): Read
    {
        $sku = trim($sku);
        $this->read = new Read();
        $this->read->FullRead("
            SELECT i.id as item_nfe_id,
                   i.nfe_id,
                   i.produto_id,
                   i.variacao_id,
                   i.quantidade,
                   i.valor_unitario,
                   i.valor_total,
                   p.nome as nome_produto,
                   p.SKU as sku,
                   p.categoria,
                   v.tamanho,
                   c.nome as cor
            FROM {$this->tableItens} i
            INNER JOIN produtos p ON i.produto_id = p.id
            INNER JOIN produtos_variations v ON i.variacao_id = v.id
            LEFT JOIN cores c ON v.cor = c.id
            WHERE i.nfe_id = :nfe_id
              AND p.SKU = :sku
            LIMIT 1
        ", "nfe_id={$nfeId}&sku={$sku}");
        return $this->read;
    }

    /**
     * Verificar se todos os itens da NF-e foram conferidos
     */
    public function todosItensConferidos(int $nfeId): bool
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as pendentes
            FROM {$this->tableItens} i
            WHERE i.nfe_id = :nfe_id
              AND NOT EXISTS (
                  SELECT 1 FROM conferencia_recebimento cr
                  WHERE cr.nfe_id = i.nfe_id
                    AND cr.produto_id = i.produto_id
                    AND cr.variacao_id = i.variacao_id
                    AND cr.status_conferencia = 'concluida'
              )
        ", "nfe_id={$nfeId}");

        $result = $this->read->getResult();
        return (int) ($result[0]['pendentes'] ?? 0) === 0;
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
        $this->read->FullRead("
            SELECT nfe.*,
                   f.nome as fornecedor_nome
            FROM {$this->table} nfe
            LEFT JOIN usuarios f ON nfe.fornecedor_id = f.id
            WHERE nfe.status = 'pendente'
            ORDER BY nfe.data_recebimento DESC
        ");
        return $this->read;
    }
}
