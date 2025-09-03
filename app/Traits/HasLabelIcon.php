<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Closure;
use Filament\Forms\Components\Concerns\HasIcons;
use Illuminate\Contracts\Support\Htmlable;

trait HasLabelIcon
{
    use HasIcons;

    protected bool | Closure $isLabelIconHidden = false;

    public function isLabelIconHidden(): bool
    {
        return (bool) $this->evaluate($this->isLabelIconHidden);
    }

    public function hiddenLabelIcon(bool | Closure $condition = true): static
    {
        $this->isLabelIconHidden = $condition;

        return $this;
    }

    /**
     * @param  array-key  $value
     */
    public function hasLabelIcon($value): bool
    {
        return array_key_exists($value, $this->getIcons());
    }

    /**
     * @param  array-key  $value
     */
    public function getLabelIcon(mixed $value): string | BackedEnum | Htmlable | null
    {
        return $this->getIcon($value);
    }
}
