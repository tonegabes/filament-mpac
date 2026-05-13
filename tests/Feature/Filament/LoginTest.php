<?php

declare(strict_types=1);

use App\Enums\Permissions\SystemPermissions;
use App\Filament\Pages\Auth\Login;
use App\Models\User;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\LdapUserService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Mockery::close();
});

beforeEach(function (): void {
    Config::set('auth.mode', 'local');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Permission::firstOrCreate(['name' => SystemPermissions::PanelsViewAdmin->value]);
});

it('renders login page with email field when auth mode is local', function (): void {
    Livewire::test(Login::class)
        ->assertOk()
        ->assertSeeHtml('wire:model="data.email"')
        ->assertDontSeeHtml('wire:model="data.username"');
});

it('renders login page with username field when auth mode is ldap', function (): void {
    Config::set('auth.mode', 'ldap');

    Livewire::test(Login::class)
        ->assertOk()
        ->assertSeeHtml('wire:model="data.username"')
        ->assertDontSeeHtml('wire:model="data.email"');
});

it('throws exception when auth mode is invalid', function (): void {
    Config::set('auth.mode', 'workos');

    expect(fn () => Livewire::test(Login::class))
        ->toThrow(InvalidArgumentException::class);
});

it('authenticates with local credentials when auth mode is local', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(SystemPermissions::PanelsViewAdmin->value);

    Livewire::test(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'password')
        ->set('data.remember', false)
        ->call('authenticate');

    $this->assertAuthenticatedAs($user);
});

it('authenticates with ldap credentials when user can access panel', function (): void {
    Config::set('auth.mode', 'ldap');
    Config::set('auth.ldap.requires_local', true);

    $user = User::factory()->create(['username' => 'johndoe']);
    $user->givePermissionTo(SystemPermissions::PanelsViewAdmin->value);

    $ldapUser = Mockery::mock(LdapUser::class);
    $ldapUserService = Mockery::mock(LdapUserService::class);
    $ldapAuthService = Mockery::mock(LdapAuthService::class);
    $ldapAuthService->emailDomain = '@mpac.mp.br';

    $ldapUserService->shouldReceive('findUserByUsername')
        ->once()
        ->with('johndoe')
        ->andReturn($ldapUser);

    $ldapAuthService->shouldReceive('authenticate')
        ->once()
        ->with('johndoe', 'password')
        ->andReturn(true);

    $this->instance(LdapUserService::class, $ldapUserService);
    $this->instance(LdapAuthService::class, $ldapAuthService);

    Livewire::test(Login::class)
        ->set('data.username', 'johndoe')
        ->set('data.password', 'password')
        ->set('data.remember', false)
        ->call('authenticate');

    $this->assertAuthenticatedAs($user);
});

it('blocks ldap login when user cannot access panel', function (): void {
    Config::set('auth.mode', 'ldap');
    Config::set('auth.ldap.requires_local', true);

    User::factory()->create(['username' => 'denied-user']);

    $ldapUser = Mockery::mock(LdapUser::class);
    $ldapUserService = Mockery::mock(LdapUserService::class);
    $ldapAuthService = Mockery::mock(LdapAuthService::class);
    $ldapAuthService->emailDomain = '@mpac.mp.br';

    $ldapUserService->shouldReceive('findUserByUsername')
        ->once()
        ->with('denied-user')
        ->andReturn($ldapUser);

    $ldapAuthService->shouldReceive('authenticate')
        ->once()
        ->with('denied-user', 'password')
        ->andReturn(true);

    $this->instance(LdapUserService::class, $ldapUserService);
    $this->instance(LdapAuthService::class, $ldapAuthService);

    Livewire::test(Login::class)
        ->set('data.username', 'denied-user')
        ->set('data.password', 'password')
        ->set('data.remember', false)
        ->call('authenticate')
        ->assertHasErrors();

    $this->assertGuest();
});
