<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Closure;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

trait HasInputIcon
{
    protected string | BackedEnum | Htmlable | null $defaultInputIcon = null;

    protected string | BackedEnum | Htmlable | null $selectedInputIcon = null;

    protected bool | Closure $isInputIconHidden = false;

    protected bool | Closure $isInputIconSemiHidden = false;

    protected bool | Closure $isInputIconLeft = false;

    public function hasInputIcon(): bool
    {
        return $this->defaultInputIcon !== null || $this->selectedInputIcon !== null;
    }

    public function getDefaultInputIcon(): string | BackedEnum | Htmlable
    {
        return $this->defaultInputIcon ?? Phosphor::CircleThin->getLabel();
    }

    public function defaultInputIcon(string | BackedEnum | Htmlable | null $defaultInputIcon): static
    {
        if ($defaultInputIcon instanceof BackedEnum && $defaultInputIcon instanceof HasLabel) {
            $defaultInputIcon = $defaultInputIcon->getLabel();
        }

        $this->defaultInputIcon = $defaultInputIcon;

        return $this;
    }

    public function selectedInputIcon(string | BackedEnum | Htmlable | null $selectedInputIcon): static
    {
        if ($selectedInputIcon instanceof BackedEnum && $selectedInputIcon instanceof HasLabel) {
            $selectedInputIcon = $selectedInputIcon->getLabel();
        }

        $this->selectedInputIcon = $selectedInputIcon;

        return $this;
    }

    public function getSelectedInputIcon(): string | BackedEnum | Htmlable
    {
        return $this->selectedInputIcon ?? $this->defaultInputIcon ?? Phosphor::CheckCircleFill->getLabel();
    }

    public function hiddenInputIcon(bool | Closure $condition = true): static
    {
        $this->isInputIconHidden = $condition;

        return $this;
    }

    public function isInputIconHidden(): bool
    {
        return (bool) $this->evaluate($this->isInputIconHidden);
    }

    public function semiHiddenInputIcon(bool | Closure $condition = true): static
    {
        $this->isInputIconSemiHidden = $condition;

        return $this;
    }

    public function isInputIconSemiHidden(): bool
    {
        return (bool) $this->evaluate($this->isInputIconSemiHidden);
    }

    public function inputIconLeft(bool | Closure $condition = true): static
    {
        $this->isInputIconLeft = $condition;

        return $this;
    }

    public function isInputIconLeft(): bool
    {
        return (bool) $this->evaluate($this->isInputIconLeft);
    }
}
