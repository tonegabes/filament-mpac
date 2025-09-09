<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use App\Traits\HasExtraTexts;
use App\Traits\HasIndicator;
use App\Traits\HasOptionIcon;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Support\Enums\IconPosition;

class CheckboxCards extends CheckboxList
{
    use HasExtraTexts;
    use HasIndicator;
    use HasOptionIcon;

    protected bool | Closure $isItemsCenter = false;

    protected string $view = 'filament.components.forms.checkbox-cards';

    protected IconPosition $iconPosition = IconPosition::Before;

    public function isItemsCenter(): bool
    {
        return (bool) $this->evaluate($this->isItemsCenter);
    }

    public function itemsCenter(bool | Closure $condition = true): static
    {
        $this->isItemsCenter = $condition;

        return $this;
    }
}
