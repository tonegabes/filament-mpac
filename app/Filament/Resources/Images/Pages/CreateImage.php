<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImage extends CreateRecord
{
    protected static string $resource = ImageResource::class;
}
