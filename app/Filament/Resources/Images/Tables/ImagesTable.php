<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Tables;

use App\Models\Image;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection(Image::getCollection())
                    ->label('Imagem')
                ,

                TextColumn::make('name')
                    ->label('Nome')
                ,

                TextColumn::make('media.size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024, 2) . ' KB')
                ,

                TextColumn::make('media.mime_type'),
            ])
            ->filters([
                //
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
