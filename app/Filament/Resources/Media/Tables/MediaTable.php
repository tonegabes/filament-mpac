<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Tables;

use App\Enums\FileCollection;
use App\Support\FileLibrary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')
                    ->label('Prévia')
                    ->state(fn (Media $record): ?string => FileLibrary::isImage($record->mime_type) ? $record->getUrl() : null)
                    ->square()
                    ->toggleable(),

                TextColumn::make('collection_name')
                    ->label('Coleção')
                    ->formatStateUsing(fn (?string $state): string => FileLibrary::collectionLabel($state))
                    ->badge()
                    ->sortable()
                ,

                TextColumn::make('name')
                    ->label('Nome')
                    ->wrap()
                    ->searchable()
                    ->sortable()
                ,

                TextColumn::make('file_name')
                    ->label('Nome do Arquivo')
                    ->searchable()
                    ->toggleable()
                ,

                TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (?string $state): string => FileLibrary::typeLabel($state))
                    ->badge()
                ,

                TextColumn::make('size')
                    ->label('Tamanho')
                    ->alignEnd()
                    ->formatStateUsing(fn (?int $state): string => FileLibrary::formatSize($state))
                    ->sortable()
                ,

                TextColumn::make('url')
                    ->label('Link')
                    ->state('Copiar link')
                    ->color('primary')
                    ->copyable()
                    ->copyableState(fn (Media $record): string => FileLibrary::url($record))
                    ->copyMessage('Link copiado para área de transferência')
                    ->copyMessageDuration(1500)
                    ->alignCenter()
                ,

                TextColumn::make('disk')
                    ->label('Disco')
                    ->toggleable(isToggledHiddenByDefault: true)
                ,

                TextColumn::make('model_type')
                    ->toggleable(isToggledHiddenByDefault: true)
                ,

                TextColumn::make('model_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                ,

                TextColumn::make('conversions_disk')
                    ->toggleable(isToggledHiddenByDefault: true)
                ,

                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime('d-m-Y H:i:s')
                ,

                TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime('d-m-Y H:i:s')
                ,
            ])
            ->filters([
                SelectFilter::make('collection_name')
                    ->label('Coleção')
                    ->options(FileCollection::options()),

                Filter::make('images')
                    ->label('Somente imagens')
                    ->query(fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%')),

                Filter::make('documents')
                    ->label('Somente documentos')
                    ->query(fn (Builder $query): Builder => $query->where('mime_type', 'not like', 'image/%')),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
