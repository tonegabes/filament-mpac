<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Illuminate\Support\Facades\Config;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

final class LdapAuthService
{
    public string $emailDomain;

    public function __construct()
    {
        $domain = Config::string('auth.ldap.email_domain', '');
        $this->emailDomain = $domain;
    }

    /**
     * Attempt authentication with LDAP credentials.
     */
    public function authenticate(string $username, string $password): bool
    {
        $ldapLogin = $username . $this->emailDomain;

        return (new LdapUser)
            ->getConnection()
            ->auth()
            ->attempt($ldapLogin, $password)
        ;
    }
}
