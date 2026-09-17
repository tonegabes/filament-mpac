<?php

declare(strict_types=1);

use App\Filament\Actions\ViewActivitiesAction;

it('returns default name activities', function (): void {
    expect(ViewActivitiesAction::getDefaultName())->toBe('activities');
});
