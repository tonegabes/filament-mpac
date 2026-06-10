<?php

declare(strict_types=1);

namespace App\Enums;

enum Roles: string
{
    case Developer = 'Desenvolvedor';
    case Admin = 'Administrador';
    case User = 'Usuário';

    public function description(): string
    {
        return match ($this) {
            self::Developer => 'Full system access with development privileges',
            self::Admin => 'Administrative access to manage system',
            self::User => 'Regular user access',
        };
    }
}
