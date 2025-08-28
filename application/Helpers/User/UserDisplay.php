<?php

namespace Agencia\Close\Helpers\User;

class UserDisplay
{
    /**
     * Formata o nome completo para exibição (primeiro nome + primeira letra do segundo)
     * Ex: "João Silva Santos" -> "João S."
     */
    public static function formatDisplayName(string $fullName): string
    {
        $names = explode(' ', trim($fullName));
        
        if (count($names) === 1) {
            return $names[0];
        }
        
        $firstName = $names[0];
        $secondName = $names[1];
        $secondInitial = substr($secondName, 0, 1);
        
        return $firstName . ' ' . $secondInitial . '.';
    }
    
    /**
     * Gera as iniciais do nome para avatar
     * Ex: "João Silva Santos" -> "JS"
     */
    public static function getInitials(string $fullName): string
    {
        $names = explode(' ', trim($fullName));
        $initials = '';
        
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= substr($name, 0, 1);
            }
        }
        
        return strtoupper(substr($initials, 0, 2));
    }
    
    /**
     * Retorna o tipo de usuário em texto legível
     */
    public static function getUserTypeText(string $tipo): string
    {
        switch ($tipo) {
            case '1':
                return 'Administrador';
            case '2':
                return 'Funcionário';
            case '3':
                return 'Companhia';
            default:
                return 'Usuário';
        }
    }
    
    /**
     * Gera cor de fundo para avatar baseada no nome
     */
    public static function getAvatarColor(string $fullName): string
    {
        $colors = [
            'bg-primary',
            'bg-secondary', 
            'bg-success',
            'bg-info',
            'bg-warning',
            'bg-danger',
            'bg-dark',
            'bg-purple',
            'bg-pink',
            'bg-indigo'
        ];
        
        $hash = crc32($fullName);
        $index = abs($hash) % count($colors);
        
        return $colors[$index];
    }
} 