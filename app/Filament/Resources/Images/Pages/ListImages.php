<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImages extends ListRecords
{
    protected static string $resource = ImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
