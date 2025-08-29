<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images\Tables;

use App\Models\Image;
use App\Traits\HasNotifications;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ImagesTable
{
    use HasNotifications;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection(Image::COLLECTION_NAME)
                    ->label('Imagem')
                ,

                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable()
                ,

                TextColumn::make('media.url')
                    ->label('Link')
                    ->icon(Phosphor::Copy)
                    ->iconColor('primary')
                    ->color('primary')
                    ->state('Copiar Link')
                    ->alignCenter()
                    ->copyable()
                    ->copyableState(fn (Image $record) => $record->getUrl())
                    ->copyMessage('Link copiado para área de transferência')
                    ->copyMessageDuration(1500)
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
