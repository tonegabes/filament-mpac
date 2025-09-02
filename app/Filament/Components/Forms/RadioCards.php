<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use BackedEnum;
use Closure;
use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Contracts\CanDisableOptions;
use Filament\Forms\Components\Field;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Enums\GridDirection;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class RadioCards extends Field implements CanDisableOptions
{
    use Concerns\CanDisableOptions;
    use Concerns\CanDisableOptionsWhenSelectedInSiblingRepeaterItems;
    use Concerns\CanFixIndistinctState;
    use Concerns\HasDescriptions;
    use Concerns\HasExtraInputAttributes;
    use Concerns\HasGridDirection;
    use Concerns\HasIcons;
    use Concerns\HasOptions;

    /**
     * @var view-string
     */
    protected string $view = 'filament.components.forms.radio-cards';

    protected bool | Closure $isIconHidden = false;

    protected bool | Closure $isIconSemiHidden = false;

    protected bool | Closure $isDescriptionHidden = false;

    protected bool | Closure $isInputIconHidden = false;

    protected string | BackedEnum | Htmlable | null $inputIcon = null;

    public function list(): static
    {
        $this->gridDirection(GridDirection::Column);

        return $this;
    }

    public function simple(bool | Closure $condition = true): static
    {
        $this->isDescriptionHidden = $condition;
        $this->isIconHidden = $condition;

        return $this;
    }

    public function semiHiddenInputIcon(bool | Closure $condition = true): static
    {
        $this->isIconSemiHidden = $condition;

        return $this;
    }

    public function isIconSemiHidden(): bool
    {
        return (bool) $this->evaluate($this->isIconSemiHidden);
    }

    public function hasInputIcon(): bool
    {
        return $this->inputIcon !== null;
    }

    public function inputIcon(string | BackedEnum | Htmlable | null $inputIcon): static
    {
        if ($inputIcon instanceof BackedEnum && $inputIcon instanceof HasLabel) {
            $inputIcon = $inputIcon->getLabel();
        }

        $this->inputIcon = $inputIcon;

        return $this;
    }

    public function getInputIcon(): string | BackedEnum | Htmlable
    {
        return $this->inputIcon ?? Phosphor::CheckCircleFill->getLabel();
    }

    public function isInputIconHidden(): bool
    {
        return (bool) $this->evaluate($this->isInputIconHidden);
    }

    public function hiddenInputIcon(bool | Closure $condition = true): static
    {
        $this->isInputIconHidden = $condition;

        return $this;
    }

    public function isIconHidden(): bool
    {
        return (bool) $this->evaluate($this->isIconHidden);
    }

    public function hiddenIcon(bool | Closure $condition = true): static
    {
        $this->isIconHidden = $condition;

        return $this;
    }

    public function isDescriptionHidden(): bool
    {
        return (bool) $this->evaluate($this->isDescriptionHidden);
    }

    public function hiddenDescription(bool | Closure $condition = true): static
    {
        $this->isDescriptionHidden = $condition;

        return $this;
    }

    /**
     * @param  array-key  $value
     */
    public function hasIcon($value): bool
    {
        return array_key_exists($value, $this->getIcons());
    }

    /**
     * @return ?array<string>
     */
    public function getInValidationRuleValues(): ?array
    {
        $values = parent::getInValidationRuleValues();

        if ($values !== null) {
            return $values;
        }

        return array_keys($this->getEnabledOptions());
    }

    public function hasNullableBooleanState(): bool
    {
        return true;
    }
}
