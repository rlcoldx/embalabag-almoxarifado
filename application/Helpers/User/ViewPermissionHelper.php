<?php

namespace Agencia\Close\Helpers\User;

use Agencia\Close\Models\User\User;
use Agencia\Close\Models\User\Permissao;

class ViewPermissionHelper
{
    private static $userPermissions = null;
    private static $userType = null;
    
    /**
     * Verifica se o usuário logado tem permissão para uma ação específica
     */
    public static function can($modulo, $acao = 'visualizar')
    {
        if (!isset($_SESSION[BASE.'user_id'])) {
            return false;
        }
        
        $userType = self::getUserType();
        
        // Administradores têm acesso total
        if ($userType === '1') {
            return true;
        }
        
        // Companhias têm acesso limitado (apenas visualização)
        if ($userType === '3') {
            return $acao === 'visualizar' && in_array($modulo, ['produtos', 'estoque', 'relatorios']);
        }
        
        // Funcionários: verificar permissões através dos cargos
        if ($userType === '2') {
            $permissions = self::getUserPermissions();
            foreach ($permissions as $permission) {
                if ($permission['modulo'] === $modulo && $permission['acao'] === $acao) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Verifica se o usuário pode visualizar um módulo
     */
    public static function canView($modulo)
    {
        return self::can($modulo, 'visualizar');
    }
    
    /**
     * Verifica se o usuário pode criar no módulo
     */
    public static function canCreate($modulo)
    {
        return self::can($modulo, 'criar');
    }
    
    /**
     * Verifica se o usuário pode editar no módulo
     */
    public static function canEdit($modulo)
    {
        return self::can($modulo, 'editar');
    }
    
    /**
     * Verifica se o usuário pode excluir no módulo
     */
    public static function canDelete($modulo)
    {
        return self::can($modulo, 'excluir');
    }
    
    /**
     * Verifica se o usuário pode executar uma ação específica
     */
    public static function canExecute($modulo, $acao)
    {
        return self::can($modulo, $acao);
    }
    
    /**
     * Retorna o tipo do usuário logado
     */
    public static function getUserType()
    {
        if (self::$userType === null) {
            self::$userType = $_SESSION[BASE.'user_tipo'] ?? null;
        }
        return self::$userType;
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
     * Retorna todas as permissões do usuário logado
     */
    public static function getUserPermissions()
    {
        if (self::$userPermissions === null) {
            if (!isset($_SESSION[BASE.'user_id'])) {
                self::$userPermissions = [];
                return self::$userPermissions;
            }
            
            $userType = self::getUserType();
            
            // Administradores têm todas as permissões
            if ($userType === '1') {
                $permissao = new Permissao();
                $todasPermissoes = $permissao->getAllPermissoes();
                self::$userPermissions = $todasPermissoes->getResult() ?? [];
            }
            // Funcionários: buscar permissões dos cargos
            else if ($userType === '2') {
                $user = new User();
                $permissoes = $user->getPermissoesDoUsuario($_SESSION[BASE.'user_id']);
                self::$userPermissions = $permissoes->getResult() ?? [];
            }
            // Companhias: permissões limitadas
            else {
                self::$userPermissions = [];
            }
        }
        
        return self::$userPermissions;
    }
    
    /**
     * Retorna permissões agrupadas por módulo
     */
    public static function getPermissionsByModule()
    {
        $permissions = self::getUserPermissions();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $modulo = $permission['modulo'];
            if (!isset($grouped[$modulo])) {
                $grouped[$modulo] = [];
            }
            $grouped[$modulo][] = $permission['acao'];
        }
        
        return $grouped;
    }
    
    /**
     * Verifica se o usuário tem qualquer permissão em um módulo
     */
    public static function hasAnyPermissionInModule($modulo)
    {
        $permissions = self::getPermissionsByModule();
        return isset($permissions[$modulo]) && !empty($permissions[$modulo]);
    }
    
    /**
     * Retorna array com permissões para uso em templates Twig
     */
    public static function getPermissionsForTemplate()
    {
        return [
            'user_type' => self::getUserType(),
            'is_admin' => self::isAdmin(),
            'is_employee' => self::isEmployee(),
            'is_company' => self::isCompany(),
            'permissions' => self::getPermissionsByModule(),
            'can' => [
                'usuarios' => [
                    'view' => self::canView('usuarios'),
                    'create' => self::canCreate('usuarios'),
                    'edit' => self::canEdit('usuarios'),
                    'delete' => self::canDelete('usuarios')
                ],
                'produtos' => [
                    'view' => self::canView('produtos'),
                    'create' => self::canCreate('produtos'),
                    'edit' => self::canEdit('produtos'),
                    'delete' => self::canDelete('produtos')
                ],
                'armazenagens' => [
                    'view' => self::canView('armazenagens'),
                    'create' => self::canCreate('armazenagens'),
                    'edit' => self::canEdit('armazenagens'),
                    'delete' => self::canDelete('armazenagens')
                ],
                'conferencia' => [
                    'view' => self::canView('conferencia'),
                    'create' => self::canCreate('conferencia'),
                    'edit' => self::canEdit('conferencia'),
                    'delete' => self::canDelete('conferencia')
                ],
                'recebimento' => [
                    'view' => self::canView('recebimento'),
                    'create' => self::canCreate('recebimento'),
                    'edit' => self::canEdit('recebimento'),
                    'delete' => self::canDelete('recebimento')
                ],
                'notas_fiscais' => [
                    'view' => self::canView('notas_fiscais'),
                    'create' => self::canCreate('notas_fiscais'),
                    'edit' => self::canEdit('notas_fiscais'),
                    'delete' => self::canDelete('notas_fiscais')
                ],
                'movimentacoes' => [
                    'view' => self::canView('movimentacoes'),
                    'create' => self::canCreate('movimentacoes'),
                    'edit' => self::canEdit('movimentacoes'),
                    'delete' => self::canDelete('movimentacoes')
                ],
                'etiquetas' => [
                    'view' => self::canView('etiquetas'),
                    'create' => self::canCreate('etiquetas'),
                    'edit' => self::canEdit('etiquetas'),
                    'delete' => self::canDelete('etiquetas')
                ],
                'relatorios' => [
                    'view' => self::canView('relatorios'),
                    'generate' => self::canExecute('relatorios', 'gerar')
                ],
                'armazenagens' => [
                    'view' => self::canView('armazenagens'),
                    'create' => self::canCreate('armazenagens'),
                    'edit' => self::canEdit('armazenagens'),
                    'delete' => self::canDelete('armazenagens'),
                    'mapa' => self::canExecute('armazenagens', 'mapa'),
                    'transferir' => self::canExecute('armazenagens', 'transferir')
                ],
                'cargos' => [
                    'view' => self::canView('cargos'),
                    'create' => self::canCreate('cargos'),
                    'edit' => self::canEdit('cargos'),
                    'delete' => self::canDelete('cargos'),
                    'permissoes' => self::canExecute('cargos', 'permissoes')
                ]
            ]
        ];
    }
    
    /**
     * Limpa cache de permissões (útil após mudanças)
     */
    public static function clearCache()
    {
        self::$userPermissions = null;
        self::$userType = null;
    }
}
