<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Illuminate\Support\Facades\Config::set('auth.ldap.enabled', false);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders login page when LDAP is disabled', function (): void {
    Livewire::test(Login::class)
        ->assertOk();
});
