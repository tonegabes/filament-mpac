<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\User;
use App\Support\ActivityLog;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewActivitiesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'activities';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Histórico')
            ->icon(Phosphor::ClockCounterClockwise)
            ->color('gray')
            ->slideOver()
            ->modalHeading(fn (Model $record): string => 'Histórico' . self::recordHeadingSuffix($record))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->disabledSchema()
            ->visible(function (?Model $record): bool {
                $user = Auth::user();

                if (! $user instanceof User || $record === null) {
                    return false;
                }

                if (! $user->can('viewAny', Activity::class)) {
                    return false;
                }

                return method_exists($record, 'activitiesAsSubject');
            })
            ->schema([
                RepeatableEntry::make('history')
                    ->hiddenLabel()
                    ->placeholder('Nenhuma atividade registrada.')
                    ->contained(false)
                    ->state(function (Model $record): array {
                        if (! method_exists($record, 'activitiesAsSubject')) {
                            return [];
                        }

                        return $record->activitiesAsSubject()
                            ->with('causer')
                            ->latest('id')
                            ->limit(30)
                            ->get()
                            ->all();
                    })
                    ->schema([
                        TextEntry::make('event')
                            ->label('Evento')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ActivityLog::eventLabel($state))
                            ->color(fn (?string $state): string => ActivityLog::eventColor($state)),

                        TextEntry::make('causer.name')
                            ->label('Usuário')
                            ->placeholder('Sistema'),

                        TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(3),
            ]);
    }

    private static function recordHeadingSuffix(Model $record): string
    {
        $name = $record->getAttribute('name');

        if (! is_string($name) || $name === '') {
            return '';
        }

        return ': ' . $name;
    }
}
