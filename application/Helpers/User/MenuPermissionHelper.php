<?php

namespace Agencia\Close\Helpers\User;

class MenuPermissionHelper
{
    /**
     * Verifica se um item de menu deve ser exibido
     */
    public static function shouldShowMenuItem($menuItem)
    {
        if (!isset($_SESSION[BASE.'user_id'])) {
            return false;
        }
        
        $userType = $_SESSION[BASE.'user_tipo'] ?? null;
        
        // Administradores veem tudo
        if ($userType === '1') {
            return true;
        }
        
        // Mapeamento de itens de menu e suas permissões necessárias
        $menuPermissions = [
            // Menu de Usuários
            'users' => ['modulo' => 'usuarios', 'acao' => 'visualizar'],
            'users_create' => ['modulo' => 'usuarios', 'acao' => 'criar'],
            'users_edit' => ['modulo' => 'usuarios', 'acao' => 'editar'],
            'users_delete' => ['modulo' => 'usuarios', 'acao' => 'excluir'],
            
            // Menu de Cargos
            'cargos' => ['modulo' => 'cargos', 'acao' => 'visualizar'],
            'cargos_create' => ['modulo' => 'cargos', 'acao' => 'criar'],
            'cargos_edit' => ['modulo' => 'cargos', 'acao' => 'editar'],
            'cargos_delete' => ['modulo' => 'cargos', 'acao' => 'excluir'],
            'cargos_permissoes' => ['modulo' => 'cargos', 'acao' => 'permissoes'],
            
            // Menu de Produtos
            'produtos' => ['modulo' => 'produtos', 'acao' => 'visualizar'],
            'produtos_new' => ['modulo' => 'produtos', 'acao' => 'criar'],
            'produtos_edit' => ['modulo' => 'produtos', 'acao' => 'editar'],
            'produtos_delete' => ['modulo' => 'produtos', 'acao' => 'excluir'],
            'estoque_baixo' => ['modulo' => 'produtos', 'acao' => 'visualizar'],
            'categorias' => ['modulo' => 'produtos', 'acao' => 'visualizar'],
            'cores' => ['modulo' => 'produtos', 'acao' => 'visualizar'],
            
            // Menu de Armazenagens
            'armazenagens' => ['modulo' => 'armazenagens', 'acao' => 'visualizar'],
            'armazenagens_new' => ['modulo' => 'armazenagens', 'acao' => 'criar'],
            'armazenagens_edit' => ['modulo' => 'armazenagens', 'acao' => 'editar'],
            'armazenagens_delete' => ['modulo' => 'armazenagens', 'acao' => 'excluir'],
            'armazenagens_mapa' => ['modulo' => 'armazenagens', 'acao' => 'mapa'],
            'armazenagens_transferir' => ['modulo' => 'armazenagens', 'acao' => 'transferir'],
            
            // Menu de Conferência
            'conferencia' => ['modulo' => 'conferencia', 'acao' => 'visualizar'],
            'conferencia_new' => ['modulo' => 'conferencia', 'acao' => 'criar'],
            'conferencia_edit' => ['modulo' => 'conferencia', 'acao' => 'editar'],
            'conferencia_delete' => ['modulo' => 'conferencia', 'acao' => 'excluir'],
            'conferencia_relatorio' => ['modulo' => 'relatorios', 'acao' => 'gerar'],
            
            // Menu de Recebimentos
            'recebimento_dashboard' => ['modulo' => 'recebimento', 'acao' => 'visualizar'],
            'recebimento_nf' => ['modulo' => 'notas_fiscais', 'acao' => 'visualizar'],
            'recebimento_movimentacoes' => ['modulo' => 'movimentacoes', 'acao' => 'visualizar'],
            'recebimento_etiquetas' => ['modulo' => 'etiquetas', 'acao' => 'visualizar'],
            'recebimento_relatorios' => ['modulo' => 'relatorios', 'acao' => 'gerar'],
            
            // Menu de Transferências
            'transferencias' => ['modulo' => 'movimentacoes', 'acao' => 'visualizar'],
            'transferencias_create' => ['modulo' => 'movimentacoes', 'acao' => 'criar'],
            'transferencias_execute' => ['modulo' => 'movimentacoes', 'acao' => 'executar'],
        ];
        
        // Se não há mapeamento para o item, não exibir
        if (!isset($menuPermissions[$menuItem])) {
            return false;
        }
        
        $requiredPermission = $menuPermissions[$menuItem];
        
        // Companhias têm acesso limitado
        if ($userType === '3') {
            $allowedModules = ['produtos', 'estoque', 'relatorios'];
            return in_array($requiredPermission['modulo'], $allowedModules) && 
                   $requiredPermission['acao'] === 'visualizar';
        }
        
        // Funcionários: verificar permissões através dos cargos
        if ($userType === '2') {
            return ViewPermissionHelper::can($requiredPermission['modulo'], $requiredPermission['acao']);
        }
        
        return false;
    }
    
    /**
     * Retorna array com visibilidade dos itens de menu para templates
     */
    public static function getMenuVisibility()
    {
        return [
            // Usuários
            'users' => self::shouldShowMenuItem('users'),
            'users_create' => self::shouldShowMenuItem('users_create'),
            'users_edit' => self::shouldShowMenuItem('users_edit'),
            'users_delete' => self::shouldShowMenuItem('users_delete'),
            
            // Cargos
            'cargos' => self::shouldShowMenuItem('cargos'),
            'cargos_create' => self::shouldShowMenuItem('cargos_create'),
            'cargos_edit' => self::shouldShowMenuItem('cargos_edit'),
            'cargos_delete' => self::shouldShowMenuItem('cargos_delete'),
            'cargos_permissoes' => self::shouldShowMenuItem('cargos_permissoes'),
            
            // Produtos
            'produtos' => self::shouldShowMenuItem('produtos'),
            'produtos_new' => self::shouldShowMenuItem('produtos_new'),
            'produtos_edit' => self::shouldShowMenuItem('produtos_edit'),
            'produtos_delete' => self::shouldShowMenuItem('produtos_delete'),
            'estoque_baixo' => self::shouldShowMenuItem('estoque_baixo'),
            'categorias' => self::shouldShowMenuItem('categorias'),
            'cores' => self::shouldShowMenuItem('cores'),
            
            // Armazenagens
            'armazenagens' => self::shouldShowMenuItem('armazenagens'),
            'armazenagens_new' => self::shouldShowMenuItem('armazenagens_new'),
            'armazenagens_edit' => self::shouldShowMenuItem('armazenagens_edit'),
            'armazenagens_delete' => self::shouldShowMenuItem('armazenagens_delete'),
            'armazenagens_mapa' => self::shouldShowMenuItem('armazenagens_mapa'),
            'armazenagens_transferir' => self::shouldShowMenuItem('armazenagens_transferir'),
            
            // Conferência
            'conferencia' => self::shouldShowMenuItem('conferencia'),
            'conferencia_new' => self::shouldShowMenuItem('conferencia_new'),
            'conferencia_edit' => self::shouldShowMenuItem('conferencia_edit'),
            'conferencia_delete' => self::shouldShowMenuItem('conferencia_delete'),
            'conferencia_relatorio' => self::shouldShowMenuItem('conferencia_relatorio'),
            
            // Recebimentos
            'recebimento_dashboard' => self::shouldShowMenuItem('recebimento_dashboard'),
            'recebimento_nf' => self::shouldShowMenuItem('recebimento_nf'),
            'recebimento_movimentacoes' => self::shouldShowMenuItem('recebimento_movimentacoes'),
            'recebimento_etiquetas' => self::shouldShowMenuItem('recebimento_etiquetas'),
            'recebimento_relatorios' => self::shouldShowMenuItem('recebimento_relatorios'),
            
            // Transferências
            'transferencias' => self::shouldShowMenuItem('transferencias'),
            'transferencias_create' => self::shouldShowMenuItem('transferencias_create'),
            'transferencias_execute' => self::shouldShowMenuItem('transferencias_execute'),
        ];
    }
}
