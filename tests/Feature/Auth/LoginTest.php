<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Livewire\Livewire;
use Mpac\FilamentAccessControl\Enums\UserRole;
use Mpac\FilamentAuth\Enums\AuthMode;
use Mpac\FilamentAuth\Ldap\LdapAuthService;
use Mpac\FilamentAuth\Ldap\LdapUserService;
use Mpac\FilamentAuth\Pages\Login;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('auth.mode', AuthMode::Hybrid->value);
    Config::set('auth.default_tab', AuthMode::Ldap->value);
    Config::set('auth.ldap.requires_local', true);
    Config::set('auth.ldap.email_domain', '@mpac.mp.br');
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('displays MPAC User and External User tabs on the login page', function (): void {
    $this->get('/login')
        ->assertSuccessful()
        ->assertSee('Usuário MPAC')
        ->assertSee('Usuário Externo');

    Livewire::test(Login::class)
        ->assertOk()
        ->assertSet('activeTab', AuthMode::Ldap->value)
        ->assertSeeHtml('wire:model="data.username"')
        ->assertDontSeeHtml('wire:model="data.email"')
        ->set('activeTab', AuthMode::Local->value)
        ->assertSeeHtml('wire:model="data.email"')
        ->assertDontSeeHtml('wire:model="data.username"');
});

it('authenticates MPAC users via LDAP tab and external users via local tab', function (): void {
    /** @var User $ldapUser */
    $ldapUser = $this->createMpacUser(UserRole::User, [
        'name' => 'Servidor MPAC',
        'username' => 'servidor.mpac',
        'email' => 'servidor.mpac@mpac.mp.br',
        'auth_driver' => AuthMode::Ldap->value,
    ]);

    /** @var User $localUser */
    $localUser = $this->createMpacUser(UserRole::User, [
        'name' => 'Usuário Externo',
        'username' => 'externo@example.com',
        'email' => 'externo@example.com',
        'password' => 'SecretPass123!',
        'auth_driver' => AuthMode::Local->value,
    ]);

    $this->instance(LdapUserService::class, new class extends LdapUserService
    {
        public function findUserByUsername(string $username): LdapUser
        {
            return new LdapUser([
                'displayName' => ['Servidor MPAC'],
                'mail' => ['servidor.mpac@mpac.mp.br'],
            ]);
        }
    });

    $this->instance(LdapAuthService::class, new class extends LdapAuthService
    {
        public function authenticate(string $username, string $password): bool
        {
            return $username === 'servidor.mpac' && $password === 'ldap-secret';
        }
    });

    Livewire::test(Login::class)
        ->assertSet('activeTab', AuthMode::Ldap->value)
        ->set('data.username', 'servidor.mpac')
        ->set('data.password', 'ldap-secret')
        ->call('authenticate')
        ->assertHasNoErrors();

    $this->assertAuthenticatedAs($ldapUser);

    auth()->logout();

    Livewire::test(Login::class)
        ->set('activeTab', AuthMode::Local->value)
        ->set('data.email', $localUser->email)
        ->set('data.password', 'SecretPass123!')
        ->call('authenticate')
        ->assertHasNoErrors();

    $this->assertAuthenticatedAs($localUser);
});

it('prevents cross-driver authentication across login tabs', function (): void {
    /** @var User $ldapUser */
    $ldapUser = $this->createMpacUser(UserRole::User, [
        'name' => 'Servidor MPAC',
        'username' => 'servidor.mpac',
        'email' => 'servidor.mpac@mpac.mp.br',
        'password' => 'LocalFallbackPass123!',
        'auth_driver' => AuthMode::Ldap->value,
    ]);

    /** @var User $localUser */
    $localUser = $this->createMpacUser(UserRole::User, [
        'name' => 'Usuário Externo',
        'username' => 'externo.teste',
        'email' => 'externo@example.com',
        'password' => 'LocalPass123!',
        'auth_driver' => AuthMode::Local->value,
    ]);

    Livewire::test(Login::class)
        ->set('activeTab', AuthMode::Local->value)
        ->set('data.email', $ldapUser->email)
        ->set('data.password', 'LocalFallbackPass123!')
        ->call('authenticate')
        ->assertHasErrors(['data.email']);

    $this->assertGuest();

    $this->instance(LdapUserService::class, new class extends LdapUserService
    {
        public function findUserByUsername(string $username): LdapUser
        {
            return new LdapUser([
                'displayName' => ['Usuário Externo'],
                'mail' => ['externo@example.com'],
            ]);
        }
    });

    $this->instance(LdapAuthService::class, new class extends LdapAuthService
    {
        public function authenticate(string $username, string $password): bool
        {
            return true;
        }
    });

    Livewire::test(Login::class)
        ->set('activeTab', AuthMode::Ldap->value)
        ->set('data.username', $localUser->username)
        ->set('data.password', 'any-password')
        ->call('authenticate')
        ->assertHasErrors(['data.username']);

    $this->assertGuest();
});
