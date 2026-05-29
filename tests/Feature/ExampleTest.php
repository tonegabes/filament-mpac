<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects the public login route to the Filament login page', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->get('/entrar')
        ->assertRedirect((string) Filament::getLoginUrl());
});
