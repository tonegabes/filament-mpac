<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageLayouts: string implements HasLabel
{
    case Split = 'layouts.auth.split';
    case Centered = 'layouts.auth.centered';
    case FullPage = 'layouts.auth.fullpage';

    public function getLabel(): string
    {
        return  str_replace('layouts.auth.', '', $this->value);
    }
}
