<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\AuthMode;
use Illuminate\Support\Facades\Config;

final class AuthModeHandlerResolver
{
    public function __construct(
        private readonly LocalAuthModeHandler $localAuthModeHandler,
        private readonly LdapAuthModeHandler $ldapAuthModeHandler,
    ) {
    }

    public function resolve(AuthMode $authMode): AuthModeHandler
    {
        return match ($authMode) {
            AuthMode::Local => $this->localAuthModeHandler,
            AuthMode::Ldap  => $this->ldapAuthModeHandler,
        };
    }

    public function resolveFromConfig(): AuthModeHandler
    {
        $authMode = AuthMode::fromConfig(Config::string('auth.mode'));

        return $this->resolve($authMode);
    }
}
