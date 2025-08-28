<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewImage extends ViewRecord
{
    protected static string $resource = ImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
