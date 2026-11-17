<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\RolePermissions;

class RolePolicy extends ResourcePolicy
{
    protected static string $permissions = RolePermissions::class;
}
