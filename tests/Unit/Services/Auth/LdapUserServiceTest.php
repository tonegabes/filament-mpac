<?php

declare(strict_types=1);

use App\Services\Auth\LdapUserService;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

it('getUserInfo returns attribute value when present', function (): void {
    $ldapUser = new LdapUser([
        'mail' => ['user@corp.com'],
    ]);

    $service = new LdapUserService;

    expect($service->getUserInfo('mail', $ldapUser))->toBe('user@corp.com');
});

it('getUserInfo returns empty string when attribute is missing', function (): void {
    $ldapUser = new LdapUser;

    $service = new LdapUserService;

    expect($service->getUserInfo('displayName', $ldapUser))->toBe('');
});

it('getUserInfo returns empty string when attribute is not string', function (): void {
    $ldapUser = new LdapUser([
        'count' => [123],
    ]);

    $service = new LdapUserService;

    expect($service->getUserInfo('count', $ldapUser))->toBe('');
});
