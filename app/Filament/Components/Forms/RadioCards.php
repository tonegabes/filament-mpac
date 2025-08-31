<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Contracts\CanDisableOptions;
use Filament\Forms\Components\Field;

class RadioCards extends Field implements CanDisableOptions
{
    use Concerns\CanDisableOptions;
    use Concerns\CanDisableOptionsWhenSelectedInSiblingRepeaterItems;
    use Concerns\CanFixIndistinctState;
    use Concerns\HasDescriptions;
    use Concerns\HasExtraInputAttributes;
    use Concerns\HasGridDirection;
    use Concerns\HasOptions;

    /**
     * @var view-string
     */
    protected string $view = 'filament.components.forms.radio-cards';

    // protected bool | Closure $isInline = false;

    //     public function boolean(?string $trueLabel = null, ?string $falseLabel = null): static
    //     {
    //         $this->options([
    //             1 => $trueLabel ?? __('filament-forms::components.radio.boolean.true'),
    //             0 => $falseLabel ?? __('filament-forms::components.radio.boolean.false'),
    //         ]);
    //
    //         $this->stateCast(app(BooleanStateCast::class, ['isStoredAsInt' => true]));
    //
    //         return $this;
    //     }

    //     public function inline(bool | Closure $condition = true): static
    //     {
    //         $this->isInline = $condition;
    //
    //         return $this;
    //     }
    //
    //     public function isInline(): bool
    //     {
    //         return (bool) $this->evaluate($this->isInline);
    //     }

    // public function getDefaultState(): mixed
    // {
    //     return parent::getDefaultState();
    // }

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
