<?php

declare(strict_types=1);

use App\Services\Auth\AuthModeHandlerResolver;
use App\Services\Auth\LdapAuthModeHandler;
use App\Services\Auth\LocalAuthModeHandler;
use Illuminate\Support\Facades\Config;

it('resolves local auth handler from configuration', function (): void {
    Config::set('auth.mode', 'local');

    $handler = app(AuthModeHandlerResolver::class)->resolveFromConfig();

    expect($handler)->toBeInstanceOf(LocalAuthModeHandler::class)
        ->and($handler->allowsLocalRegistration())->toBeTrue();
});

it('resolves ldap auth handler from configuration', function (): void {
    Config::set('auth.mode', 'ldap');

    $handler = app(AuthModeHandlerResolver::class)->resolveFromConfig();

    expect($handler)->toBeInstanceOf(LdapAuthModeHandler::class)
        ->and($handler->allowsLocalRegistration())->toBeFalse();
});

it('throws exception for invalid auth mode from configuration', function (): void {
    Config::set('auth.mode', 'workos');

    expect(fn () => app(AuthModeHandlerResolver::class)->resolveFromConfig())
        ->toThrow(InvalidArgumentException::class);
});
