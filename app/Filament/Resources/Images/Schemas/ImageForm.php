<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Schemas;

use App\Enums\FileCollection;
use App\Filament\Support\LibraryFileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class ImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('name')->default('Image Name Not Set'),

                LibraryFileUpload::mediaLibrary('image', FileCollection::Images, 'Imagem'),
            ]);
    }
}
