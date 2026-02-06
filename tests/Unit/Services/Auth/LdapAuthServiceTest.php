<?php

declare(strict_types=1);

use App\Services\Auth\LdapAuthService;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('auth.ldap.email_domain', '');
});

it('reads email domain from config', function (): void {
    Config::set('auth.ldap.email_domain', '@company.com');

    $service = new LdapAuthService;

    expect($service->emailDomain)->toBe('@company.com');
});

it('uses empty string when email domain is not set', function (): void {
    $service = new LdapAuthService;

    expect($service->emailDomain)->toBe('');
});

it('builds login string as username plus email domain', function (): void {
    Config::set('auth.ldap.email_domain', '@corp.local');
    $service = new LdapAuthService;

    $username = 'johndoe';
    $ldapLogin = $username . $service->emailDomain;

    expect($ldapLogin)->toBe('johndoe@corp.local');
});
