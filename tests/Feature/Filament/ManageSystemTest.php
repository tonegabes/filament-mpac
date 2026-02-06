<?php

declare(strict_types=1);

use App\Enums\NavGroups;
use App\Enums\Permissions\SystemPermissions;
use App\Filament\Pages\Settings\ManageSystem;
use App\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::firstOrCreate(['name' => SystemPermissions::SystemSettingsManage->value]);
});

it('returns settings as navigation group', function (): void {
    expect(ManageSystem::getNavigationGroup())->toBe(NavGroups::Settings->value);
});

it('allows access when user has SystemSettingsManage permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(SystemPermissions::SystemSettingsManage->value);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ManageSystem::class)
        ->assertOk();
});

it('denies access when user lacks SystemSettingsManage permission', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ManageSystem::class)
        ->assertForbidden();
});

it('denies access when not authenticated', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ManageSystem::class)
        ->assertForbidden();
});
