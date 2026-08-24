<?php

namespace Agencia\Close\Helpers\User;

use Agencia\Close\Models\User\User;

class ResponsavelHelper
{
    public static function listar(): array
    {
        return (new User())->getUsuariosAtivos();
    }

    public static function idFromPost(string $field, ?int $fallback = null): ?int
    {
        $value = $_POST[$field] ?? null;
        if ($value === null || $value === '') {
            return $fallback;
        }

        $id = (int) $value;
        return $id > 0 ? $id : $fallback;
    }

    public static function opcoesFiltro(): array
    {
        $opcoes = ['' => 'Todos'];
        foreach (self::listar() as $usuario) {
            $opcoes[(string) $usuario['id']] = $usuario['nome'];
        }

        return $opcoes;
    }

    public static function nomePorId(?int $id): string
    {
        if (!$id) {
            return '';
        }

        foreach (self::listar() as $usuario) {
            if ((int) $usuario['id'] === $id) {
                return (string) $usuario['nome'];
            }
        }

        return '';
    }
}
