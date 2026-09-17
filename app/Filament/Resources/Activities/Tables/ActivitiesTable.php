<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Resources\Activities\ActivityResource;
use App\Support\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table, bool $forSubject = false): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ActivityLog::eventLabel($state))
                    ->color(fn (?string $state): string => ActivityLog::eventColor($state))
                    ->sortable(),

                TextColumn::make('subject_type')
                    ->label('Assunto')
                    ->formatStateUsing(fn (Activity $record): string => ActivityLog::subjectLabel($record))
                    ->wrap()
                    ->visible(! $forSubject),

                TextColumn::make('causer.name')
                    ->label('Usuário')
                    ->placeholder('Sistema')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with($forSubject ? ['causer'] : ['causer', 'subject']))
            ->paginationPageOptions([50, 100])
            ->filters([
                SelectFilter::make('event')
                    ->label('Evento')
                    ->options(ActivityLog::eventOptions()),

                SelectFilter::make('subject_type')
                    ->label('Assunto')
                    ->options(ActivityLog::subjectTypeOptions())
                    ->visible(! $forSubject),

                Filter::make('created_at')
                    ->label('Data')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('De'),
                        DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, mixed $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, mixed $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Activity $record): string => ActivityResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
