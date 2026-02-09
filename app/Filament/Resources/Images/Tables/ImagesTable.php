<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Tables;

use Alsaloul\ImageGallery\Tables\Columns\ImageGalleryColumn;
use App\Filament\Actions\CopyFileUrlAction;
use App\Models\Image;
use App\Traits\HasNotifications;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesTable
{
    use HasNotifications;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageGalleryColumn::make('image')
                    ->getStateUsing(fn (Image $record) => $record->getFileUrl())
                    ->label('Imagem')
                ,

                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable()
                ,

                TextColumn::make('media.size')
                    ->label('Tamanho')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024, 2) . ' KB')
                ,

                TextColumn::make('media.mime_type')
                    ->label('Tipo')
                    ->badge()
                ,
            ])
            ->filters([
                //
            ])
            ->recordActions([
                CopyFileUrlAction::make()->label('Link da Imagem'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
