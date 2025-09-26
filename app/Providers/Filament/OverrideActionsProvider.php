<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Illuminate\Support\ServiceProvider;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class OverrideActionsProvider extends ServiceProvider
{
    public function boot(): void
    {
        CreateAction::configureUsing(function (CreateAction $action) {
            $action->icon(Phosphor::Plus->getLabel());
        });

        EditAction::configureUsing(function (EditAction $action) {
            $action
                ->icon(Phosphor::PencilSimpleLine->getLabel())
                ->color(Color::Indigo)
            ;
        });

        DeleteAction::configureUsing(function (DeleteAction $action) {
            $action
                ->icon(Phosphor::Trash->getLabel())
                ->color(Color::Rose)
            ;
        });
    }
}
