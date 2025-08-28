<?php

namespace Agencia\Close\Helpers\User;

use Agencia\Close\Models\User\User;

class PermissionHelper
{
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
        
        // Companhias têm acesso limitado (será implementado depois)
        if ($tipo === '3') {
            return false; // Por enquanto, sem acesso
        }
        
        // Funcionários: verificar permissões através dos cargos
        if ($tipo === '2') {
            $user = new User();
            return $user->usuarioTemPermissao($usuarioId, $modulo, $acao);
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
        
        // Companhias têm acesso limitado
        if ($tipo === '3') {
            return false; // Por enquanto, sem acesso
        }
        
        // Funcionários: verificar permissões através dos cargos
        if ($tipo === '2') {
            $user = new User();
            $permissoes = $user->getPermissoesDoUsuario($_SESSION[BASE.'user_id']);
            
            if ($permissoes->getResult()) {
                foreach ($permissoes->getResult() as $permissao) {
                    if ($permissao['modulo'] === $modulo) {
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