<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Schemas;

use App\Models\Image;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ImageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection(Image::getCollection())
                ,

                TextEntry::make('name'),
            ]);
    }
}
