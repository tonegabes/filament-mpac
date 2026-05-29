<?php

declare(strict_types=1);

use App\Enums\Permissions\PanelPermissions;
use App\Models\Permission;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('canAccessPanel returns true when user has panel view permission', function (): void {
    Permission::firstOrCreate(['name' => PanelPermissions::ViewAdmin->value]);
    $user = User::factory()->create();
    $user->givePermissionTo(PanelPermissions::ViewAdmin->value);

    $panel = Panel::make()->id('admin');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('canAccessPanel returns false when user lacks panel view permission', function (): void {
    $user = User::factory()->create();

    $panel = Panel::make()->id('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('canAccessPanel returns false when panel is null', function (): void {
    $user = User::factory()->create();

    expect($user->canAccessPanel(null))->toBeFalse();
});
