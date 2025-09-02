<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum PageLayouts: string implements HasDescription, HasIcon, HasLabel
{
    case Split = 'layouts.auth.split';
    case Centered = 'layouts.auth.centered';
    case FullPage = 'layouts.auth.fullpage';

    public function getLabel(): string
    {
        return match ($this) {
            self::Split    => 'Dividido',
            self::Centered => 'Centralizado',
            self::FullPage => 'Tela inteira',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Split    => (string) Phosphor::ColumnsThin->getLabel(),
            self::Centered => (string) Phosphor::ArrowsInThin->getLabel(),
            self::FullPage => (string) Phosphor::ArrowsOutSimpleThin->getLabel(),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Split    => 'Divide a página em duas colunas',
            self::Centered => 'Centraliza o conteúdo na página',
            self::FullPage => 'Ocupa a tela inteira',
        };
    }
}
