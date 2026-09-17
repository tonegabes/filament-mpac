<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Schemas;

use App\Support\ActivityLog;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('event')
                                    ->label('Evento')
                                    ->badge()
                                    ->formatStateUsing(fn (?string $state): string => ActivityLog::eventLabel($state))
                                    ->color(fn (?string $state): string => ActivityLog::eventColor($state)),

                                TextEntry::make('created_at')
                                    ->label('Data')
                                    ->dateTime('d/m/Y H:i:s'),

                                TextEntry::make('subject_type')
                                    ->label('Assunto')
                                    ->formatStateUsing(fn (Activity $record): string => ActivityLog::subjectLabel($record)),

                                TextEntry::make('causer.name')
                                    ->label('Usuário')
                                    ->placeholder('Sistema'),

                                TextEntry::make('description')
                                    ->label('Descrição')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Alterações')
                    ->schema([
                        KeyValueEntry::make('old')
                            ->label('Antes')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->state(fn (Activity $record): array => ActivityLog::oldValues($record)),

                        KeyValueEntry::make('attributes')
                            ->label('Depois')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->state(fn (Activity $record): array => ActivityLog::newValues($record)),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (Activity $record): bool => ActivityLog::hasChanges($record)),
            ]);
    }
}
