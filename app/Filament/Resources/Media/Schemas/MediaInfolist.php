<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Schemas;

use App\Support\FileLibrary;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('preview')
                    ->label('Prévia')
                    ->state(fn (Media $record): ?string => FileLibrary::isImage($record->mime_type) ? $record->getUrl() : null),

                TextEntry::make('name')
                    ->label('Nome'),

                TextEntry::make('file_name')
                    ->label('Nome do arquivo'),

                TextEntry::make('collection_name')
                    ->label('Coleção')
                    ->formatStateUsing(fn (?string $state): string => FileLibrary::collectionLabel($state))
                    ->badge(),

                TextEntry::make('mime_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (?string $state): string => FileLibrary::typeLabel($state))
                    ->badge(),

                TextEntry::make('size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn (?int $state): string => FileLibrary::formatSize($state)),

                TextEntry::make('url')
                    ->label('Link')
                    ->state(fn (Media $record): string => FileLibrary::url($record))
                    ->copyable()
                    ->copyMessage('Link copiado para área de transferência'),
            ]);
    }
}
