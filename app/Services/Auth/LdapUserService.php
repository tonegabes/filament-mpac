<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\DirectoryUnavailableException;
use Illuminate\Support\Facades\Log;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class LdapUserService
{
    /**
     * Find LDAP user by username (samAccountName).
     *
     * @throws DirectoryUnavailableException
     */
    public function findUserByUsername(string $username): ?LdapUser
    {
        try {
            $result = LdapUser::query()
                ->where('samAccountName', $username)
                ->first();
        } catch (LdapRecordException $e) {
            Log::error('Erro ao buscar usuário no LDAP.', ['exception' => $e]);

            throw new DirectoryUnavailableException(
                'Não foi possível consultar o serviço de diretório. Tente novamente mais tarde.',
                previous: $e,
            );
        }

        return $result instanceof LdapUser ? $result : null;
    }

    /**
     * Find LDAP user by email.
     *
     * @throws DirectoryUnavailableException
     */
    public function findUserByEmail(string $email): ?LdapUser
    {
        try {
            $result = LdapUser::query()
                ->where('mail', $email)
                ->first();
        } catch (LdapRecordException $e) {
            Log::error('Erro ao buscar usuário no LDAP por e-mail.', ['exception' => $e]);

            throw new DirectoryUnavailableException(
                'Não foi possível consultar o serviço de diretório. Tente novamente mais tarde.',
                previous: $e,
            );
        }

        return $result instanceof LdapUser ? $result : null;
    }

    /**
     * Get user info from LDAP.
     */
    public function getUserInfo(string $attribute, LdapUser $ldapUser): string
    {
        $value = $ldapUser->getFirstAttribute($attribute);

        return is_string($value) ? $value : '';
    }
}
