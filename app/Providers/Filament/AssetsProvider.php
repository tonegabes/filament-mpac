<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AssetsProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentAsset::register([
            AlpineComponent::make('checkbox', resource_path('js/components/checkbox.js'))
                ->loadedOnRequest(),
        ]);
    }
}
