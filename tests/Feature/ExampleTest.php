<?php

declare(strict_types=1);

use Filament\Facades\Filament;

it('redirects the public login route to the Filament login page', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->get('/login')
        ->assertRedirect((string) Filament::getLoginUrl());
});
