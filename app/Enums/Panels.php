<?php

declare(strict_types=1);

namespace App\Enums;

enum Panels: string
{
    case Admin = 'admin';

    public function path(): string
    {
        return match ($this) {
            self::Admin => 'admin',
        };
    }
}
