<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Closure;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

trait HasIndicator
{
    protected string | BackedEnum | Htmlable | null $defaultIndicator = null;

    protected string | BackedEnum | Htmlable | null $selectedIndicator = null;

    protected bool | Closure $showIndicator = true;

    protected bool | Closure $isIndicatorPartiallyHidden = false;

    protected IconPosition $indicatorPosition = IconPosition::Before;

    public function hasIndicator(): bool
    {
        return $this->defaultIndicator !== null || $this->selectedIndicator !== null;
    }

    public function getDefaultIndicator(): string | BackedEnum | Htmlable
    {
        return $this->defaultIndicator ?? Phosphor::CircleThin->getLabel();
    }

    public function defaultIndicator(string | BackedEnum | Htmlable | null $defaultIndicator): static
    {
        if ($defaultIndicator instanceof BackedEnum && $defaultIndicator instanceof HasLabel) {
            $defaultIndicator = $defaultIndicator->getLabel();
        }

        $this->defaultIndicator = $defaultIndicator;

        return $this;
    }

    public function selectedIndicator(string | BackedEnum | Htmlable | null $selectedIndicator): static
    {
        if ($selectedIndicator instanceof BackedEnum && $selectedIndicator instanceof HasLabel) {
            $selectedIndicator = $selectedIndicator->getLabel();
        }

        $this->selectedIndicator = $selectedIndicator;

        return $this;
    }

    public function getSelectedIndicator(): string | BackedEnum | Htmlable
    {
        return $this->selectedIndicator ?? $this->defaultIndicator ?? Phosphor::RecordFill->getLabel();
    }

    public function hiddenIndicator(bool | Closure $condition = true): static
    {
        $this->showIndicator = ! $condition;

        return $this;
    }

    public function showIndicator(): bool
    {
        return (bool) $this->evaluate($this->showIndicator);
    }

    public function partiallyHiddenIndicator(bool | Closure $condition = true): static
    {
        $this->isIndicatorPartiallyHidden = $condition;

        return $this;
    }

    public function isIndicatorPartiallyHidden(): bool
    {
        return (bool) $this->evaluate($this->isIndicatorPartiallyHidden);
    }

    public function indicatorPosition(IconPosition $position): static
    {
        $this->indicatorPosition = $position;

        return $this;
    }

    public function hasIndicatorBefore(): bool
    {
        return $this->indicatorPosition === IconPosition::Before;
    }

    public function hasIndicatorAfter(): bool
    {
        return $this->indicatorPosition === IconPosition::After;
    }

    public function indicatorBefore(): static
    {
        $this->indicatorPosition = IconPosition::Before;

        return $this;
    }

    public function indicatorAfter(): static
    {
        $this->indicatorPosition = IconPosition::After;

        return $this;
    }
}
