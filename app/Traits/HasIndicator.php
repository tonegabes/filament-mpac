<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Closure;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

trait HasIndicator
{
    protected string | BackedEnum | Htmlable | null $defaultIndicator = null;

    protected string | BackedEnum | Htmlable | null $selectedIndicator = null;

    protected bool | Closure $isIndicatorHidden = false;

    protected bool | Closure $isIndicatorPartiallyHidden = false;

    protected bool | Closure $isIndicatorLeft = false;

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
        return $this->selectedIndicator ?? $this->defaultIndicator ?? Phosphor::CheckCircleFill->getLabel();
    }

    public function hiddenIndicator(bool | Closure $condition = true): static
    {
        $this->isIndicatorHidden = $condition;

        return $this;
    }

    public function isIndicatorHidden(): bool
    {
        return (bool) $this->evaluate($this->isIndicatorHidden);
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

    public function indicatorLeft(bool | Closure $condition = true): static
    {
        $this->isIndicatorLeft = $condition;

        return $this;
    }

    public function isIndicatorLeft(): bool
    {
        return (bool) $this->evaluate($this->isIndicatorLeft);
    }
}
