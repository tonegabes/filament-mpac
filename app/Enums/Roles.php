<?php

declare(strict_types=1);

namespace App\Enums;

enum Roles: string
{
    case Developer = 'Desenvolvedor';
    case Admin = 'Administrador';
    case Operator = 'Operador';
    case Guest = 'Visitante';

    /**
     * Get role description.
     */
    public function description(): string
    {
        return match ($this) {
            self::Developer => 'Full system access with development privileges',
            self::Admin => 'Administrative access to manage system',
            self::Operator => 'Operational access to use the system',
            self::Guest => 'Limited access for viewing only',
        };
    }

    /**
     * Check if role has admin privileges.
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::Developer, self::Admin]);
    }

    /**
     * Check if role has development privileges.
     */
    public function isDeveloper(): bool
    {
        return $this === self::Developer;
    }

    /**
     * Get role level (higher number = more privileges).
     */
    public function level(): int
    {
        return match ($this) {
            self::Developer => 4,
            self::Admin => 3,
            self::Operator => 2,
            self::Guest => 1,
        };
    }
}
