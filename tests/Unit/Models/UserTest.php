<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Mockery::close();
});

it('canAccessPanel returns true when user has panel view permission', function (): void {
    Permission::firstOrCreate(['name' => 'system.panels.view.admin']);
    $user = User::factory()->create();
    $user->givePermissionTo('system.panels.view.admin');

    $panel = Mockery::mock(Filament\Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('canAccessPanel returns false when user lacks panel view permission', function (): void {
    $user = User::factory()->create();

    $panel = Mockery::mock(Filament\Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('canAccessPanel returns false when panel is null', function (): void {
    $user = User::factory()->create();

    expect($user->canAccessPanel(null))->toBeFalse();
});
