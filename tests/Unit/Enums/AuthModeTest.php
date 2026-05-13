<?php

declare(strict_types=1);

use App\Enums\AuthMode;

it('resolves configured auth mode values', function (): void {
    expect(AuthMode::fromConfig('local'))->toBe(AuthMode::Local)
        ->and(AuthMode::fromConfig('ldap'))->toBe(AuthMode::Ldap)
        ->and(AuthMode::fromConfig(' LDAP '))->toBe(AuthMode::Ldap);
});

it('throws exception for unknown auth mode values', function (): void {
    expect(fn () => AuthMode::fromConfig('workos'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => AuthMode::fromConfig(null))
        ->toThrow(InvalidArgumentException::class);
});

it('exposes capabilities per auth mode', function (): void {
    expect(AuthMode::Local->usesUsernameField())->toBeFalse()
        ->and(AuthMode::Local->allowsLocalRegistration())->toBeTrue()
        ->and(AuthMode::Ldap->usesUsernameField())->toBeTrue()
        ->and(AuthMode::Ldap->allowsLocalRegistration())->toBeFalse();
});
