<?php

namespace Agencia\Close\Models\Etiqueta;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class EtiquetaInterna extends Model
{
    public function getAllEtiquetas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            ORDER BY ei.created_at DESC
        ");
        return $this->read;
    }

    public function getEtiquetaById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE ei.id = :id
        ", "id={$id}");
        return $this->read;
    }

    public function getEtiquetaByCodigo(string $codigo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE ei.codigo = :codigo
        ", "codigo={$codigo}");
        return $this->read;
    }

    public function getEtiquetasByTipo(string $tipo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE ei.tipo_etiqueta = :tipo
            ORDER BY ei.created_at DESC
        ", "tipo={$tipo}");
        return $this->read;
    }

    public function getEtiquetasByReferencia(int $referenciaId, string $referenciaTipo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE ei.referencia_id = :referencia_id AND ei.referencia_tipo = :referencia_tipo
            ORDER BY ei.created_at DESC
        ", "referencia_id={$referenciaId}&referencia_tipo={$referenciaTipo}");
        return $this->read;
    }

    public function getEtiquetasByStatus(string $status): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE ei.status = :status
            ORDER BY ei.created_at DESC
        ", "status={$status}");
        return $this->read;
    }

    public function createEtiqueta(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("etiquetas_internas", $data);
        return $this->create->getResult();
    }

    public function updateEtiqueta(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("etiquetas_internas", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteEtiqueta(int $id): bool
    {
        $this->delete = new Delete();
        $this->delete->ExeDelete("etiquetas_internas", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function gerarCodigoEtiqueta(string $tipo, ?int $referenciaId = null): string
    {
        $prefixo = strtoupper(substr($tipo, 0, 3));
        $timestamp = time();
        $random = rand(100, 999);
        
        if ($referenciaId) {
            return "{$prefixo}{$referenciaId}{$timestamp}{$random}";
        }
        
        return "{$prefixo}{$timestamp}{$random}";
    }

    public function criarEtiquetaLocalizacao(int $armazenagemId, int $usuarioId): int|false
    {
        // Buscar dados da armazenagem
        $this->read = new Read();
        $this->read->FullRead("
            SELECT codigo, descricao, setor, tipo FROM armazenagens WHERE id = :id
        ", "id={$armazenagemId}");
        $armazenagem = $this->read->getResult();
        
        if (!$armazenagem) {
            return false;
        }
        
        $arm = $armazenagem[0];
        $codigo = $this->gerarCodigoEtiqueta('localizacao', $armazenagemId);
        
        $conteudo = [
            'tipo' => 'Localização',
            'codigo' => $arm['codigo'],
            'descricao' => $arm['descricao'],
            'setor' => $arm['setor'],
            'tipo_armazenagem' => $arm['tipo']
        ];
        
        $data = [
            'codigo' => $codigo,
            'tipo_etiqueta' => 'localizacao',
            'referencia_id' => $armazenagemId,
            'referencia_tipo' => 'armazenagem',
            'conteudo' => json_encode($conteudo),
            'codigo_barras' => $codigo,
            'usuario_criacao' => $usuarioId,
            'status' => 'criada'
        ];
        
        return $this->createEtiqueta($data);
    }

    public function criarEtiquetaProduto(int $itemNfId, int $usuarioId): int|false
    {
        // Buscar dados do item da NF
        $this->read = new Read();
        $this->read->FullRead("
            SELECT pnf.codigo_produto, pnf.descricao_produto, pnf.quantidade,
                   nf.numero as numero_nf, nf.fornecedor
            FROM pedidos_nf pnf
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            WHERE pnf.id = :id
        ", "id={$itemNfId}");
        $item = $this->read->getResult();
        
        if (!$item) {
            return false;
        }
        
        $itemData = $item[0];
        $codigo = $this->gerarCodigoEtiqueta('produto', $itemNfId);
        
        $conteudo = [
            'tipo' => 'Produto',
            'codigo_produto' => $itemData['codigo_produto'],
            'descricao' => $itemData['descricao_produto'],
            'quantidade' => $itemData['quantidade'],
            'numero_nf' => $itemData['numero_nf'],
            'fornecedor' => $itemData['fornecedor']
        ];
        
        $data = [
            'codigo' => $codigo,
            'tipo_etiqueta' => 'produto',
            'referencia_id' => $itemNfId,
            'referencia_tipo' => 'item_nf',
            'conteudo' => json_encode($conteudo),
            'codigo_barras' => $codigo,
            'usuario_criacao' => $usuarioId,
            'status' => 'criada'
        ];
        
        return $this->createEtiqueta($data);
    }

    public function marcarComoImpressa(int $id): bool
    {
        $data = [
            'status' => 'impressa',
            'data_impressao' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->updateEtiqueta($id, $data);
    }

    public function marcarComoAplicada(int $id): bool
    {
        $data = [
            'status' => 'aplicada',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->updateEtiqueta($id, $data);
    }

    public function gerarQRCode(string $conteudo): string
    {
        // Aqui você pode implementar a geração de QR Code
        // Por enquanto, retornamos uma string simples
        return base64_encode($conteudo);
    }

    public function buscarEtiquetas(array $filtros = []): Read
    {
        $this->read = new Read();
        $sql = "
            SELECT ei.*, 
                   u.nome as usuario_criacao_nome
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['codigo'])) {
            $sql .= " AND ei.codigo LIKE :codigo";
            $params .= "codigo=%{$filtros['codigo']}%&";
        }
        
        if (!empty($filtros['tipo_etiqueta'])) {
            $sql .= " AND ei.tipo_etiqueta = :tipo_etiqueta";
            $params .= "tipo_etiqueta={$filtros['tipo_etiqueta']}&";
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND ei.status = :status";
            $params .= "status={$filtros['status']}&";
        }
        
        if (!empty($filtros['usuario_criacao'])) {
            $sql .= " AND ei.usuario_criacao = :usuario_criacao";
            $params .= "usuario_criacao={$filtros['usuario_criacao']}&";
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND ei.created_at >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND ei.created_at <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }
        
        $sql .= " ORDER BY ei.created_at DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        return $this->read;
    }

    public function getEstatisticasEtiquetas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT 
                COUNT(*) as total_etiquetas,
                SUM(CASE WHEN ei.status = 'criada' THEN 1 ELSE 0 END) as etiquetas_criadas,
                SUM(CASE WHEN ei.status = 'impressa' THEN 1 ELSE 0 END) as etiquetas_impressas,
                SUM(CASE WHEN ei.status = 'aplicada' THEN 1 ELSE 0 END) as etiquetas_aplicadas,
                SUM(CASE WHEN ei.status = 'inativa' THEN 1 ELSE 0 END) as etiquetas_inativas,
                SUM(CASE WHEN ei.tipo_etiqueta = 'localizacao' THEN 1 ELSE 0 END) as etiquetas_localizacao,
                SUM(CASE WHEN ei.tipo_etiqueta = 'produto' THEN 1 ELSE 0 END) as etiquetas_produto,
                SUM(CASE WHEN ei.tipo_etiqueta = 'palete' THEN 1 ELSE 0 END) as etiquetas_palete,
                SUM(CASE WHEN ei.tipo_etiqueta = 'caixa' THEN 1 ELSE 0 END) as etiquetas_caixa
            FROM etiquetas_internas ei
        ");
        return $this->read;
    }

    public function getAll(): Read
    {
        return $this->getAllEtiquetas();
    }

    public function getById(int $id): Read
    {
        return $this->getEtiquetaById($id);
    }

    public function create(array $data): int|false
    {
        return $this->createEtiqueta($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateEtiqueta($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteEtiqueta($id);
    }

    public function aplicar(int $id): bool
    {
        return $this->marcarComoAplicada($id);
    }

    public function getAllItens(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT pnf.*, 
                   nf.numero as numero_nf,
                   nf.fornecedor,
                   nf.data_emissao
            FROM pedidos_nf pnf
            INNER JOIN notas_fiscais nf ON pnf.nota_fiscal_id = nf.id
            WHERE pnf.status = 'recebido'
            ORDER BY pnf.id DESC
        ");
        return $this->read;
    }

    public function gerarRelatorioEtiquetas(array $filtros = []): array
    {
        $this->read = new Read();
        $sql = "
            SELECT 
                ei.*,
                u.nome as usuario_criacao_nome,
                CASE 
                    WHEN ei.tipo_etiqueta = 'localizacao' THEN 'Localização'
                    WHEN ei.tipo_etiqueta = 'produto' THEN 'Produto'
                    WHEN ei.tipo_etiqueta = 'pallet' THEN 'Pallet'
                    WHEN ei.tipo_etiqueta = 'caixa' THEN 'Caixa'
                    ELSE ei.tipo_etiqueta
                END as tipo_etiqueta_descricao,
                CASE 
                    WHEN ei.status = 'criada' THEN 'Criada'
                    WHEN ei.status = 'impressa' THEN 'Impressa'
                    WHEN ei.status = 'aplicada' THEN 'Aplicada'
                    WHEN ei.status = 'inativa' THEN 'Inativa'
                    ELSE ei.status
                END as status_descricao,
                DATE_FORMAT(ei.data_criacao, '%d/%m/%Y %H:%i') as data_criacao_formatada,
                DATE_FORMAT(ei.data_impressao, '%d/%m/%Y %H:%i') as data_impressao_formatada,
                DATE_FORMAT(ei.data_aplicacao, '%d/%m/%Y %H:%i') as data_aplicacao_formatada
            FROM etiquetas_internas ei
            INNER JOIN usuarios u ON ei.usuario_criacao = u.id
            WHERE 1=1
        ";
        $params = "";
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND ei.data_criacao >= :data_inicio";
            $params .= "data_inicio={$filtros['data_inicio']}&";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND ei.data_criacao <= :data_fim";
            $params .= "data_fim={$filtros['data_fim']}&";
        }
        
        if (!empty($filtros['tipo_etiqueta'])) {
            $sql .= " AND ei.tipo_etiqueta = :tipo_etiqueta";
            $params .= "tipo_etiqueta={$filtros['tipo_etiqueta']}&";
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND ei.status = :status";
            $params .= "status={$filtros['status']}&";
        }
        
        if (!empty($filtros['usuario_criacao'])) {
            $sql .= " AND ei.usuario_criacao = :usuario_criacao";
            $params .= "usuario_criacao={$filtros['usuario_criacao']}&";
        }
        
        if (!empty($filtros['codigo'])) {
            $sql .= " AND ei.codigo LIKE :codigo";
            $params .= "codigo=%{$filtros['codigo']}%&";
        }
        
        $sql .= " ORDER BY ei.data_criacao DESC";
        
        $this->read->FullRead($sql, rtrim($params, '&'));
        $result = $this->read->getResult();
        
        return [
            'dados' => $result ?: [],
            'total_registros' => count($result ?: []),
            'total_etiquetas' => count($result ?: []),
            'etiquetas_criadas' => count(array_filter($result ?: [], function($item) {
                return $item['status'] === 'criada';
            })),
            'etiquetas_impressas' => count(array_filter($result ?: [], function($item) {
                return $item['status'] === 'impressa';
            })),
            'etiquetas_aplicadas' => count(array_filter($result ?: [], function($item) {
                return $item['status'] === 'aplicada';
            })),
            'etiquetas_localizacao' => count(array_filter($result ?: [], function($item) {
                return $item['tipo_etiqueta'] === 'localizacao';
            })),
            'etiquetas_produto' => count(array_filter($result ?: [], function($item) {
                return $item['tipo_etiqueta'] === 'produto';
            })),
            'periodo' => [
                'inicio' => $filtros['data_inicio'] ?? null,
                'fim' => $filtros['data_fim'] ?? null
            ]
        ];
    }
} 