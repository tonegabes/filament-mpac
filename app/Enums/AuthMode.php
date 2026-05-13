<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum AuthMode: string
{
    case Local = 'local';
    case Ldap = 'ldap';

    public static function fromConfig(?string $mode): self
    {
        $normalizedMode = strtolower(trim((string) $mode));
        $authMode = self::tryFrom($normalizedMode);

        if ($authMode instanceof self) {
            return $authMode;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid auth mode [%s]. Supported modes: %s.',
                $normalizedMode === '' ? 'empty' : $normalizedMode,
                implode(', ', array_map(static fn (self $mode): string => $mode->value, self::cases())),
            ),
        );
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
