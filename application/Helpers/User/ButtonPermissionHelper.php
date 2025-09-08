<?php

namespace Agencia\Close\Helpers\User;

class ButtonPermissionHelper
{
    /**
     * Verifica se um botão deve ser exibido
     */
    public static function shouldShowButton($buttonType, $modulo = null, $acao = null)
    {
        if (!isset($_SESSION[BASE.'user_id'])) {
            return false;
        }
        
        $userType = $_SESSION[BASE.'user_tipo'] ?? null;
        
        // Administradores veem todos os botões
        if ($userType === '1') {
            return true;
        }
        
        // Companhias têm acesso limitado
        if ($userType === '3') {
            $allowedModules = ['produtos', 'estoque', 'relatorios'];
            return $modulo && in_array($modulo, $allowedModules) && $acao === 'visualizar';
        }
        
        // Funcionários: verificar permissões específicas
        if ($userType === '2') {
            if ($modulo && $acao) {
                return ViewPermissionHelper::can($modulo, $acao);
            }
            
            // Mapeamento de botões específicos
            $buttonPermissions = [
                'create' => ['acao' => 'criar'],
                'edit' => ['acao' => 'editar'],
                'delete' => ['acao' => 'excluir'],
                'view' => ['acao' => 'visualizar'],
                'save' => ['acao' => 'criar'],
                'update' => ['acao' => 'editar'],
                'remove' => ['acao' => 'excluir'],
                'print' => ['acao' => 'imprimir'],
                'export' => ['acao' => 'gerar'],
                'import' => ['acao' => 'criar'],
                'approve' => ['acao' => 'aprovar'],
                'execute' => ['acao' => 'executar'],
                'cancel' => ['acao' => 'cancelar'],
                'receive' => ['acao' => 'receber'],
                'move' => ['acao' => 'movimentar'],
                'transfer' => ['acao' => 'executar'],
            ];
            
            if (isset($buttonPermissions[$buttonType])) {
                $requiredAction = $buttonPermissions[$buttonType]['acao'];
                return $modulo ? ViewPermissionHelper::can($modulo, $requiredAction) : false;
            }
        }
        
        return false;
    }
    
    /**
     * Verifica se botão de criar deve ser exibido
     */
    public static function canCreate($modulo)
    {
        return self::shouldShowButton('create', $modulo, 'criar');
    }
    
    /**
     * Verifica se botão de editar deve ser exibido
     */
    public static function canEdit($modulo)
    {
        return self::shouldShowButton('edit', $modulo, 'editar');
    }
    
    /**
     * Verifica se botão de excluir deve ser exibido
     */
    public static function canDelete($modulo)
    {
        return self::shouldShowButton('delete', $modulo, 'excluir');
    }
    
    /**
     * Verifica se botão de visualizar deve ser exibido
     */
    public static function canView($modulo)
    {
        return self::shouldShowButton('view', $modulo, 'visualizar');
    }
    
    /**
     * Verifica se botão de imprimir deve ser exibido
     */
    public static function canPrint($modulo)
    {
        return self::shouldShowButton('print', $modulo, 'imprimir');
    }
    
    /**
     * Verifica se botão de exportar deve ser exibido
     */
    public static function canExport($modulo)
    {
        return self::shouldShowButton('export', $modulo, 'gerar');
    }
    
    /**
     * Verifica se botão de aprovar deve ser exibido
     */
    public static function canApprove($modulo)
    {
        return self::shouldShowButton('approve', $modulo, 'aprovar');
    }
    
    /**
     * Verifica se botão de executar deve ser exibido
     */
    public static function canExecute($modulo)
    {
        return self::shouldShowButton('execute', $modulo, 'executar');
    }
    
    /**
     * Verifica se botão de cancelar deve ser exibido
     */
    public static function canCancel($modulo)
    {
        return self::shouldShowButton('cancel', $modulo, 'cancelar');
    }
    
    /**
     * Verifica se botão de receber deve ser exibido
     */
    public static function canReceive($modulo)
    {
        return self::shouldShowButton('receive', $modulo, 'receber');
    }
    
    /**
     * Verifica se botão de movimentar deve ser exibido
     */
    public static function canMove($modulo)
    {
        return self::shouldShowButton('move', $modulo, 'movimentar');
    }
    
    /**
     * Retorna array com visibilidade dos botões para templates
     */
    public static function getButtonVisibility($modulo = null)
    {
        return [
            'create' => self::canCreate($modulo),
            'edit' => self::canEdit($modulo),
            'delete' => self::canDelete($modulo),
            'view' => self::canView($modulo),
            'print' => self::canPrint($modulo),
            'export' => self::canExport($modulo),
            'approve' => self::canApprove($modulo),
            'execute' => self::canExecute($modulo),
            'cancel' => self::canCancel($modulo),
            'receive' => self::canReceive($modulo),
            'move' => self::canMove($modulo),
        ];
    }
    
    /**
     * Gera classes CSS para botões baseado em permissões
     */
    public static function getButtonClasses($buttonType, $modulo = null, $baseClasses = 'btn')
    {
        $canShow = self::shouldShowButton($buttonType, $modulo);
        
        if (!$canShow) {
            return $baseClasses . ' d-none'; // Esconde o botão
        }
        
        return $baseClasses;
    }
    
    /**
     * Gera atributos disabled para botões baseado em permissões
     */
    public static function getButtonDisabled($buttonType, $modulo = null)
    {
        $canShow = self::shouldShowButton($buttonType, $modulo);
        return $canShow ? '' : 'disabled';
    }
}
