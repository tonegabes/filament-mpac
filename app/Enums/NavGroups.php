<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum NavGroups: string implements HasIcon, HasLabel
{
    case Authorization = 'Autorização';
    case Tools = 'Ferramentas';
    case Settings = 'Configurações';
    case Files = 'Arquivos';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Authorization => (string) Phosphor::ShieldCheck->getLabel(),
            self::Tools         => (string) Phosphor::Wrench->getLabel(),
            self::Settings      => (string) Phosphor::Gear->getLabel(),
            self::Files         => (string) Phosphor::File->getLabel(),
        };
    }
}
