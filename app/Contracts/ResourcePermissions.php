<?php

declare(strict_types=1);

namespace App\Contracts;

use BackedEnum;

interface ResourcePermissions
{
    public static function forAction(string $action): ?BackedEnum;
}
