<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use App\Models\Image;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Collection;

class ImagePicker extends Field
{
    use HasExtraInputAttributes;
    use HasOptions;

    protected string $view = 'filament.components.forms.image-picker';

    public string $imageUrl = '#';

    public ?string $imageName = null;

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return Image::all();
    }

    //     public function options(array $options): static
    //     {
    //         $this->getState()->options = $options;
    //
    //         return $this;
    //     }
}
