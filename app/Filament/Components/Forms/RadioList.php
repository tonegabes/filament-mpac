<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use App\Traits\HasExtraTexts;
use App\Traits\HasLabelIcon;
use BackedEnum;
use Closure;
use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Contracts\CanDisableOptions;
use Filament\Forms\Components\Field;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class RadioList extends Field implements CanDisableOptions
{
    use Concerns\CanDisableOptions;
    use Concerns\CanDisableOptionsWhenSelectedInSiblingRepeaterItems;
    use Concerns\CanFixIndistinctState;
    use Concerns\HasDescriptions;
    use Concerns\HasExtraInputAttributes;
    use Concerns\HasGridDirection;
    use Concerns\HasOptions;
    use HasExtraTexts;
    use HasLabelIcon;

    /**
     * @var view-string
     */
    protected string $view = 'filament.components.forms.radio-list';

    protected bool | Closure $isDescriptionHidden = false;

    protected bool | Closure $isInputIconHidden = false;

    protected string | BackedEnum | Htmlable | null $inputIcon = null;

    public function isDescriptionHidden(): bool
    {
        return (bool) $this->evaluate($this->isDescriptionHidden);
    }

    public function hiddenDescription(bool | Closure $condition = true): static
    {
        $this->isDescriptionHidden = $condition;

        return $this;
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
}
