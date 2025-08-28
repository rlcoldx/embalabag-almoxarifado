<?php

namespace Agencia\Close\Models\User;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class Permissao extends Model
{
    private string $table = 'permissoes';
    private string $tableUsuarioPermissoes = 'usuario_permissoes';

    public function getAllPermissoes()
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, "ORDER BY modulo, acao");
        return $this->read;
    }

    public function getPermissoesPorModulo($modulo)
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, "WHERE modulo = :modulo ORDER BY acao", "modulo={$modulo}");
        return $this->read;
    }

    public function getPermissoesDoUsuario($usuarioId)
    {
        // Buscar permissões através dos cargos do usuário
        $this->read = new Read();
        $this->read->FullRead("
            SELECT DISTINCT p.* FROM permissoes p
            INNER JOIN cargo_permissoes cp ON p.id = cp.permissao_id
            INNER JOIN usuario_cargos uc ON cp.cargo_id = uc.cargo_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY p.modulo, p.acao
        ", "usuario_id={$usuarioId}");
        return $this->read;
    }

    public function usuarioTemPermissao($usuarioId, $modulo, $acao)
    {
        // Verificar se o usuário tem a permissão através dos cargos
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total FROM usuario_cargos uc
            INNER JOIN cargo_permissoes cp ON uc.cargo_id = cp.cargo_id
            INNER JOIN permissoes p ON cp.permissao_id = p.id
            WHERE uc.usuario_id = :usuario_id AND p.modulo = :modulo AND p.acao = :acao
        ", "usuario_id={$usuarioId}&modulo={$modulo}&acao={$acao}");
        
        $result = $this->read->getResult();
        return $result && $result[0]['total'] > 0;
    }

    public function concederPermissao($usuarioId, $permissaoId, $concedidoPor = null)
    {
        $data = [
            'usuario_id' => $usuarioId,
            'permissao_id' => $permissaoId,
            'concedido_por' => $concedidoPor
        ];

        $create = new Create();
        $create->ExeCreate($this->tableUsuarioPermissoes, $data);
        return $create->getResult();
    }

    public function revogarPermissao($usuarioId, $permissaoId)
    {
        $delete = new Delete();
        $delete->ExeDelete($this->tableUsuarioPermissoes, "WHERE usuario_id = :usuario_id AND permissao_id = :permissao_id", "usuario_id={$usuarioId}&permissao_id={$permissaoId}");
        $result = $delete->getResult();
        return $result === true;
    }

    public function revogarTodasPermissoes($usuarioId)
    {
        $delete = new Delete();
        $delete->ExeDelete($this->tableUsuarioPermissoes, "WHERE usuario_id = :usuario_id", "usuario_id={$usuarioId}");
        $result = $delete->getResult();
        return $result === true;
    }

    public function getPermissoesPorTipoUsuario($tipoUsuario)
    {
        // Mapeamento de permissões por tipo de usuário
        $permissoesPorTipo = [
            '1' => [ // Admin - todas as permissões
                'usuarios' => ['visualizar', 'criar', 'editar', 'excluir'],
                'cargos' => ['visualizar', 'criar', 'editar', 'excluir'],
                'produtos' => ['visualizar', 'criar', 'editar', 'excluir'],
                'estoque' => ['visualizar', 'movimentar'],
                'relatorios' => ['visualizar', 'gerar'],
                'configuracoes' => ['acessar']
            ],
            '2' => [ // Funcionário - permissões baseadas em cargos
                'usuarios' => ['visualizar'],
                'cargos' => ['visualizar'],
                'produtos' => ['visualizar', 'criar', 'editar'],
                'estoque' => ['visualizar', 'movimentar'],
                'relatorios' => ['visualizar', 'gerar'],
                'configuracoes' => []
            ],
            '3' => [ // Companhia - permissões limitadas
                'usuarios' => [],
                'cargos' => [],
                'produtos' => ['visualizar'],
                'estoque' => ['visualizar'],
                'relatorios' => ['visualizar'],
                'configuracoes' => []
            ]
        ];

        return $permissoesPorTipo[$tipoUsuario] ?? [];
    }

    public function atribuirPermissoesPorTipo($usuarioId, $tipoUsuario, $concedidoPor = null)
    {
        $permissoes = $this->getPermissoesPorTipoUsuario($tipoUsuario);
        
        // Primeiro, revoga todas as permissões existentes
        $this->revogarTodasPermissoes($usuarioId);
        
        // Depois, concede as permissões do tipo
        foreach ($permissoes as $modulo => $acoes) {
            foreach ($acoes as $acao) {
                $this->read = new Read();
                $this->read->ExeRead($this->table, "WHERE modulo = :modulo AND acao = :acao", "modulo={$modulo}&acao={$acao}");
                
                if ($this->read->getResult()) {
                    $permissaoId = $this->read->getResult()[0]['id'];
                    $this->concederPermissao($usuarioId, $permissaoId, $concedidoPor);
                }
            }
        }
        
        return true;
    }
} 