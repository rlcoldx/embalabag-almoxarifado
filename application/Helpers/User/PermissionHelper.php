<?php

namespace Agencia\Close\Helpers\User;

use Agencia\Close\Models\User\User;

class PermissionHelper
{
    private static function companyAllowedModules(): array
    {
        return ['produtos', 'estoque', 'relatorios', 'cia_aerea'];
    }

    private static function companyCan($modulo, $acao): bool
    {
        if ($modulo === 'cia_aerea') {
            return in_array($acao, ['visualizar', 'receber'], true);
        }
        return $acao === 'visualizar' && in_array($modulo, ['produtos', 'estoque', 'relatorios'], true);
    }

    /**
     * Aceita nomes no singular/plural e ações equivalentes usadas no seed.
     */
    private static function permissionCandidates(string $modulo, string $acao): array
    {
        $modulos = [$modulo];
        $moduloAliases = [
            'movimentacao' => 'movimentacoes',
            'movimentacoes' => 'movimentacao',
            'etiqueta' => 'etiquetas',
            'etiquetas' => 'etiqueta',
            'relatorio' => 'relatorios',
            'relatorios' => 'relatorio',
            'transferencias' => 'movimentacoes',
        ];
        if (isset($moduloAliases[$modulo])) {
            $modulos[] = $moduloAliases[$modulo];
        }

        $acoes = [$acao];
        $acaoAliases = [
            'criar' => ['realizar'],
            'realizar' => ['criar'],
        ];
        if (isset($acaoAliases[$acao])) {
            $acoes = array_merge($acoes, $acaoAliases[$acao]);
        }

        $pairs = [];
        foreach ($modulos as $mod) {
            foreach ($acoes as $act) {
                $pairs[] = [$mod, $act];
            }
        }

        return $pairs;
    }

    /**
     * Verifica se o usuário tem permissão para uma ação específica
     */
    public static function hasPermission($modulo, $acao)
    {
        if (!isset($_SESSION[BASE.'user_id'])) {
            return false;
        }
        
        $usuarioId = $_SESSION[BASE.'user_id'];
        $tipo = $_SESSION[BASE.'user_tipo'] ?? '';
        
        // Administradores têm acesso total
        if ($tipo === '1') {
            return true;
        }

        // Companhias: produtos/estoque/relatórios (ver) e portal cia aérea
        if ($tipo === '3') {
            foreach (self::permissionCandidates($modulo, $acao) as [$mod, $act]) {
                if (self::companyCan($mod, $act)) {
                    return true;
                }
            }
            return false;
        }
        
        // Funcionários: verificar permissões através dos cargos
        if ($tipo === '2') {
            $user = new User();
            foreach (self::permissionCandidates($modulo, $acao) as [$mod, $act]) {
                if ($user->usuarioTemPermissao($usuarioId, $mod, $act)) {
                    return true;
                }
            }
            return false;
        }
        
        return false;
    }
    
    /**
     * Verifica se o usuário tem permissão para qualquer ação de um módulo
     */
    public static function hasModulePermission($modulo)
    {
        if (!isset($_SESSION[BASE.'user_id'])) {
            return false;
        }
        
        $tipo = $_SESSION[BASE.'user_tipo'] ?? '';
        
        // Administradores têm acesso total
        if ($tipo === '1') {
            return true;
        }
        
        $modulos = array_unique(array_map(static function ($pair) {
            return $pair[0];
        }, self::permissionCandidates($modulo, 'visualizar')));

        // Companhias: acesso de visualização aos módulos liberados
        if ($tipo === '3') {
            foreach ($modulos as $mod) {
                if (in_array($mod, self::companyAllowedModules(), true)) {
                    return true;
                }
            }
            return false;
        }
        
        // Funcionários: verificar permissões através dos cargos
        if ($tipo === '2') {
            $user = new User();
            $permissoes = $user->getPermissoesDoUsuario($_SESSION[BASE.'user_id']);
            
            if ($permissoes->getResult()) {
                foreach ($permissoes->getResult() as $permissao) {
                    if (in_array($permissao['modulo'], $modulos, true)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Retorna todas as permissões do usuário
     */
    public static function getUserPermissions()
    {
        if (!isset($_SESSION[BASE.'user_id'])) {
            return [];
        }
        
        $tipo = $_SESSION[BASE.'user_tipo'] ?? '';
        
        // Administradores têm todas as permissões
        if ($tipo === '1') {
            // Retornar todas as permissões do sistema
            $permissao = new \Agencia\Close\Models\User\Permissao();
            $todasPermissoes = $permissao->getAllPermissoes();
            return $todasPermissoes->getResult() ?? [];
        }
        
        // Companhias: apenas visualização dos módulos liberados
        if ($tipo === '3') {
            $permissao = new \Agencia\Close\Models\User\Permissao();
            $todas = $permissao->getAllPermissoes()->getResult() ?? [];
            return array_values(array_filter($todas, function ($item) {
                return self::companyCan($item['modulo'] ?? '', $item['acao'] ?? '');
            }));
        }

        // Funcionários: buscar permissões dos cargos
        if ($tipo === '2') {
            $user = new User();
            $permissoes = $user->getPermissoesDoUsuario($_SESSION[BASE.'user_id']);
            return $permissoes->getResult() ?? [];
        }
        
        return [];
    }
    
    /**
     * Retorna o tipo de usuário
     */
    public static function getUserType()
    {
        return $_SESSION[BASE.'user_tipo'] ?? null;
    }
    
    /**
     * Verifica se o usuário é administrador
     */
    public static function isAdmin()
    {
        return self::getUserType() === '1';
    }
    
    /**
     * Verifica se o usuário é funcionário
     */
    public static function isEmployee()
    {
        return self::getUserType() === '2';
    }
    
    /**
     * Verifica se o usuário é companhia
     */
    public static function isCompany()
    {
        return self::getUserType() === '3';
    }
    
    /**
     * Verifica se o usuário está bloqueado
     */
    public static function isBlocked()
    {
        return $_SESSION[BASE.'user_status'] === 'inativo';
    }
    
    /**
     * Retorna o nome do tipo de usuário
     */
    public static function getUserTypeName($tipo = null)
    {
        if ($tipo === null) {
            $tipo = self::getUserType();
        }
        
        $tipos = [
            '1' => 'Administrador',
            '2' => 'Funcionário',
            '3' => 'Companhia'
        ];
        
        return $tipos[$tipo] ?? 'Desconhecido';
    }
    
    /**
     * Método de instância para verificar permissões (para uso nos controllers)
     */
    public function userHasPermission($modulo, $acao)
    {
        return self::hasPermission($modulo, $acao);
    }
    
    /**
     * Método de instância para verificar permissões de módulo
     */
    public function userHasModulePermission($modulo)
    {
        return self::hasModulePermission($modulo);
    }
} 