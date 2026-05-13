<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthMode: string
{
    case Local = 'local';
    case Ldap = 'ldap';

    public static function fromConfig(?string $mode): self
    {
        return self::tryFrom(strtolower(trim((string) $mode))) ?? self::Local;
    }

    public function usesUsernameField(): bool
    {
        return $this === self::Ldap;
    }

    public function allowsLocalRegistration(): bool
    {
        return $this === self::Local;
    }
}
