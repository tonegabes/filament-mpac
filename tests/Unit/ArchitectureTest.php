<?php

declare(strict_types=1);

arch('application uses strict types and avoids debugging helpers')
    ->expect('App')
    ->toUseStrictTypes()
    ->not->toUse(['dd', 'dump', 'die']);

arch('filament resources use Filament v5 action namespaces')
    ->expect('App\Filament\Resources')
    ->not->toUse([
        'Filament\Forms\Actions',
        'Filament\Tables\Actions',
    ]);
