<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Enums\FileCollection;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LibraryFileUpload
{
    public static function mediaLibrary(string $name, FileCollection $collection, string $label): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($name)
            ->label($label)
            ->live()
            ->required()
            ->collection($collection->value)
            ->acceptedFileTypes($collection->acceptedMimeTypes())
            ->disk($collection->disk())
            ->afterStateUpdated(function ($state, Set $set): void {
                if ($state instanceof TemporaryUploadedFile) {
                    $set('name', $state->getClientOriginalName());
                }
            });
    }

    public static function publicImage(string $name, FileCollection $collection, string $label, int $maxSize): FileUpload
    {
        $upload = FileUpload::make($name)
            ->label($label)
            ->disk($collection->disk())
            ->visibility('public')
            ->acceptedFileTypes($collection->acceptedMimeTypes())
            ->image()
            ->imageEditor()
            ->maxSize($maxSize)
            ->openable()
            ->downloadable();

        if ($collection->directory() !== null) {
            $upload->directory($collection->directory());
        }

        return $upload;
    }
}
