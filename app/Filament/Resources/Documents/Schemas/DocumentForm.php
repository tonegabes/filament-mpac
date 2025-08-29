<?php

declare(strict_types=1);

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Document;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('name')->default('Document Name Not Set'),

                SpatieMediaLibraryFileUpload::make('file')
                    ->label('Arquivo')
                    ->live()
                    ->required()
                    ->acceptedFileTypes(Document::getMimeTypeMap())
                    ->collection(Document::COLLECTION_NAME)
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state instanceof TemporaryUploadedFile) {
                            $set('name', $state->getClientOriginalName());
                        }
                    })
                ,
            ]);
    }
}
