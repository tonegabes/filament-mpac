<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),

                TextColumn::make('collection_name')
                    ->label('Coleção')
                ,

                TextColumn::make('name')
                    ->label('Nome Original')
                    ->wrap()
                    ->searchable()
                ,

                TextColumn::make('file_name')
                    ->label('Nome do Arquivo')
                ,

                TextColumn::make('mime_type')
                    ->label('Tipo de Arquivo')
                    ->badge()
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
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
