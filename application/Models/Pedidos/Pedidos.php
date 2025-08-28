<?php

namespace Agencia\Close\Models\Pedidos;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;
use Exception;

class Pedidos extends Model
{
    public function getPedidos(): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            ORDER BY p.date_create DESC
        ");
        return $read;
    }

    public function getPedido($id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            WHERE p.id = :id
        ", "id={$id}");
        return $read;
    }

    public function getPedidosPorUsuario($user_id): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            WHERE p.id_user = :user_id
            ORDER BY p.data_pedido DESC
        ", "user_id={$user_id}");
        return $read;
    }

    public function getPedidosPorStatus($status): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            WHERE p.status_pedido = :status
            ORDER BY p.date_create DESC
        ", "status={$status}");
        return $read;
    }

    public function getPedidosByStatus($status): Read
    {
        return $this->getPedidosPorStatus($status);
    }

    public function getPedidoByNumero($numero): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            WHERE p.numero_pedido = :numero
        ", "numero={$numero}");
        return $read;
    }

    public function createPedido($params): bool
    {
        try {
            $create = new Create();
            $pedido = [
                'id_user' => $params['id_user'] ?? $_SESSION['user_id'] ?? 1,
                'qtd_opcoes_p' => $params['qtd_opcoes_p'] ?? 0,
                'qtd_opcoes_m' => $params['qtd_opcoes_m'] ?? 0,
                'qtd_opcoes_g' => $params['qtd_opcoes_g'] ?? 0,
                'qtd_opcoes_bordo' => $params['qtd_opcoes_bordo'] ?? 0,
                'codigo' => $params['codigo'] ?? $this->gerarCodigo(),
                'codigo_privado' => $params['codigo_privado'] ?? null,
                'codigoSige' => $params['codigoSige'] ?? null,
                'status_sige' => $params['status_sige'] ?? null,
                'email_cliente' => $params['email_cliente'] ?? null,
                'nome_cliente' => $params['nome_cliente'] ?? null,
                'telefone_cliente' => $params['telefone_cliente'] ?? null,
                'cpf_cliente' => $params['cpf_cliente'] ?? null,
                'cep_cliente' => $params['cep_cliente'] ?? null,
                'endereco_cliente' => $params['endereco_cliente'] ?? null,
                'numero_cliente' => $params['numero_cliente'] ?? null,
                'complemento_cliente' => $params['complemento_cliente'] ?? null,
                'bairro_cliente' => $params['bairro_cliente'] ?? null,
                'cidade_cliente' => $params['cidade_cliente'] ?? null,
                'estado_cliente' => $params['estado_cliente'] ?? null,
                'localizador' => $params['localizador'] ?? null,
                'processo' => $params['processo'] ?? null,
                'base_solicitante' => $params['base_solicitante'] ?? 'Base Principal',
                'base_destino' => $params['base_destino'] ?? null,
                'numero_re' => $params['numero_re'] ?? null,
                'nome_colaborador' => $params['nome_colaborador'] ?? null,
                'voo_1' => $params['voo_1'] ?? null,
                'voo_2' => $params['voo_2'] ?? null,
                'previsao_entrega' => $params['previsao_entrega'] ?? null,
                'etiqueta_interna' => $params['etiqueta_interna'] ?? null,
                'observacoes' => $params['observacoes'] ?? null,
                'observacoes_cliente' => $params['observacoes_cliente'] ?? null,
                'observacao_adicional' => $params['observacao_adicional'] ?? null,
                'pedido_adicional' => $params['pedido_adicional'] ?? 'no',
                'valor_total' => $params['valor_total'] ?? '0.00',
                'prioridade' => $params['prioridade'] ?? 'Normal',
                'cliente_endereco' => $params['cliente_endereco'] ?? 'no',
                'pre_aprovado' => $params['pre_aprovado'] ?? 'no',
                'status_pedido' => $params['status_pedido'] ?? '1'
            ];
            
            $create->ExeCreate('pedidos', $pedido);
            return $create->getResult();
        } catch (Exception $e) {
            return false;
        }
    }

    public function updatePedido($id, $params): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos', $params, 'WHERE id = :id', "id={$id}");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function aprovarPedido($id): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos', 
                ['status_pedido' => '2'], 
                'WHERE id = :id', 
                "id={$id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function rejeitarPedido($id): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos', 
                ['status_pedido' => '0'], 
                'WHERE id = :id', 
                "id={$id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function cancelarPedido($id): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos', 
                ['status_pedido' => '0'], 
                'WHERE id = :id', 
                "id={$id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function setStatusPedido($id, $status): bool
    {
        try {
            $update = new Update();
            $update->ExeUpdate('pedidos', 
                ['status_pedido' => $status], 
                'WHERE id = :id', 
                "id={$id}"
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deletePedido($id): bool
    {
        try {
            $delete = new Delete();
            $delete->ExeDelete('pedidos', 'WHERE id = :id', "id={$id}");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function gerarCodigo(): string
    {
        $ano = date('Y');
        $mes = date('m');
        $dia = date('d');
        
        // Buscar último código do dia
        $read = new Read();
        $read->FullRead("
            SELECT codigo 
            FROM pedidos 
            WHERE codigo LIKE :prefix 
            ORDER BY id DESC 
            LIMIT 1
        ", "prefix=PED{$ano}{$mes}{$dia}%");
        
        $ultimo = $read->getResult();
        if ($ultimo) {
            $numero = intval(substr($ultimo[0]['codigo'], -4)) + 1;
        } else {
            $numero = 1;
        }
        
        return "PED{$ano}{$mes}{$dia}" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusPedido($status): string
    {
        $statusMap = [
            '0' => 'Cancelado',
            '1' => 'Pendente',
            '2' => 'Aprovado',
            '3' => 'Em andamento',
            '4' => 'Em Preparação',
            '5' => 'Aguardando Retorno Base',
            '6' => 'Enviado',
            '7' => 'Disponível para Retirada',
            '8' => 'Em Rota para Entrega',
            '9' => 'Concluído'
        ];
        
        return $statusMap[$status] ?? 'Desconhecido';
    }

    public function getPedidosPorPrioridade($prioridade): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            WHERE p.prioridade = :prioridade
            ORDER BY p.date_create DESC
        ", "prioridade={$prioridade}");
        return $read;
    }

    public function getPedidosPorBase($base): Read
    {
        $read = new Read();
        $read->FullRead("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_user = u.id
            WHERE p.base_solicitante = :base OR p.base_destino = :base
            ORDER BY p.date_create DESC
        ", "base={$base}");
        return $read;
    }
} 