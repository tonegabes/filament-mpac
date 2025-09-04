<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Closure;
use Filament\Forms\Components\Concerns\HasIcons;
use Illuminate\Contracts\Support\Htmlable;

trait HasOptionIcon
{
    use HasIcons;

    protected bool | Closure $isOptionIconHidden = false;

    public function isOptionIconHidden(): bool
    {
        return (bool) $this->evaluate($this->isOptionIconHidden);
    }

    public function hiddenOptionIcon(bool | Closure $condition = true): static
    {
        $this->isOptionIconHidden = $condition;

        return $this;
    }

    /**
     * @param  array-key  $value
     */
    public function hasOptionIcon($value): bool
    {
        return array_key_exists($value, $this->getIcons());
    }

    /**
     * @param  array-key  $value
     */
    public function getOptionIcon(mixed $value): string | BackedEnum | Htmlable | null
    {
        return $this->getIcon($value);
    }
}
