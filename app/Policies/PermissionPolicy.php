<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\PermissionPermissions;

class PermissionPolicy extends ResourcePolicy
{
    protected static string $permissions = PermissionPermissions::class;
}
