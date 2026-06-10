<?php

declare(strict_types=1);

namespace App\Enums;

use ToneGabes\Filament\Icons\Enums\Phosphor;

enum Panels: string
{
    case App = 'app';
    case Admin = 'admin';

    public function path(): string
    {
        return match ($this) {
            self::App => '',
            self::Admin => 'admin',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::App => 'App',
            self::Admin => 'Admin',
        };
    }

    public function icon(): Phosphor
    {
        return match ($this) {
            self::App => Phosphor::HouseLine,
            self::Admin => Phosphor::ShieldCheck,
        };
    }
}
