<?php

declare(strict_types=1);

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\FileCollection;
use App\Filament\Support\LibraryFileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('name')->default('Document Name Not Set'),

                LibraryFileUpload::mediaLibrary('file', FileCollection::Documents, 'Arquivo'),
            ]);
    }
}
