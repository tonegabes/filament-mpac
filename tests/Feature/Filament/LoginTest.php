<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Illuminate\Support\Facades\Config::set('auth.mode', 'local');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders login page with email field when auth mode is local', function (): void {
    Livewire::test(Login::class)
        ->assertOk()
        ->assertSeeHtml('wire:model="data.email"')
        ->assertDontSeeHtml('wire:model="data.username"');
});

it('renders login page with username field when auth mode is ldap', function (): void {
    Illuminate\Support\Facades\Config::set('auth.mode', 'ldap');

    Livewire::test(Login::class)
        ->assertOk()
        ->assertSeeHtml('wire:model="data.username"')
        ->assertDontSeeHtml('wire:model="data.email"');
});
