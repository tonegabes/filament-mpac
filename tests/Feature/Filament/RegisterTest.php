<?php

declare(strict_types=1);

use App\Enums\PageLayouts;
use App\Enums\Roles;
use App\Filament\Pages\Auth\Register;
use App\Models\User;
use App\Settings\SystemSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(Database\Seeders\PermissionSeeder::class);
    $this->seed(Database\Seeders\RoleSeeder::class);

    $settings = app(SystemSettings::class);
    $settings->enable_registration = true;
    $settings->save();

    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('creates user with username from email and is_active true and Operator role', function (): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name'                 => 'New User',
            'email'                => 'newuser@example.com',
            'password'             => 'password123',
            'passwordConfirmation' => 'password123',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name'      => 'New User',
        'email'     => 'newuser@example.com',
        'username'  => 'newuser@example.com',
        'is_active' => true,
    ]);

    $user = User::query()->where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole(Roles::Operator->value))->toBeTrue();
});

it('getLayout returns value from SystemSettings', function (): void {
    $settings = app(SystemSettings::class);
    $settings->auth_page_layout = PageLayouts::FullPage;
    $settings->save();

    $page = app(Register::class);

    expect($page->getLayout())->toBe(PageLayouts::FullPage->value);
});
