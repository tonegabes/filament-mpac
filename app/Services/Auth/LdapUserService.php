<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Traits\HasNotifications;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class LdapUserService
{
    use HasNotifications;

    /**
     * Find LDAP user by username (samAccountName).
     */
    public function findUserByUsername(string $username): ?LdapUser
    {
        try {
            $result = LdapUser::query()
                ->where('samAccountName', $username)
                ->first()
            ;
        } catch (LdapRecordException $e) {
            $this->notify('Erro ao buscar usuário no LDAP: ' . $e->getMessage(), 'danger');

            return null;
        } catch (\Exception $e) {
            throw $e;
        }

        return $result instanceof LdapUser ? $result : null;
    }

    /**
     * Find LDAP user by email.
     */
    public function findUserByEmail(string $email): ?LdapUser
    {
        $result = LdapUser::query()
            ->where('mail', $email)
            ->first()
        ;

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
