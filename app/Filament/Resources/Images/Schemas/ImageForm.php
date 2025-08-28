<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Schemas;

use App\Models\Image;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('name')->default('Image Name Not Set'),

                SpatieMediaLibraryFileUpload::make('image')
                    ->live()
                    ->image()
                    ->collection(Image::getCollection())
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state instanceof TemporaryUploadedFile) {
                            $set('name', $state->getClientOriginalName());
                        }
                    }),
            ]);
    }
}
