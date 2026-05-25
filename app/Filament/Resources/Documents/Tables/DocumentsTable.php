<?php

declare(strict_types=1);

namespace App\Filament\Resources\Documents\Tables;

use App\Filament\Actions\CopyFileUrlAction;
use App\Models\Document;
use App\Support\FileLibrary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                ,

                TextColumn::make('media.url')
                    ->label('Link')
                    ->icon(Phosphor::Copy)
                    ->iconColor('primary')
                    ->color('primary')
                    ->state('Copiar Link')
                    ->alignCenter()
                    ->copyable()
                    ->copyableState(fn (Document $record) => $record->getUrl())
                    ->copyMessage('Link copiado para área de transferência')
                    ->copyMessageDuration(1500)
                ,

                TextColumn::make('media.size')
                    ->label('Tamanho')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn (?int $state): string => FileLibrary::formatSize($state))
                ,

                TextColumn::make('media.mime_type')
                    ->label('Tipo')
                    ->badge()
                ,

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                ,
            ])
            ->filters([
                //
            ])
            ->recordActions([
                CopyFileUrlAction::make()->label('Link do Arquivo'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
