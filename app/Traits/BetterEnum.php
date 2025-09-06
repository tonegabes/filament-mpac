<?php

declare(strict_types=1);

namespace App\Traits;

trait BetterEnum
{
    /**
     * @return string[]
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }

    /**
     * @return array<string, string>
     */
    public static function asArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
