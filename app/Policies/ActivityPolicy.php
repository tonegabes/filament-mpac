<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\ActivityPermissions;

class ActivityPolicy extends ResourcePolicy
{
    protected static string $permissions = ActivityPermissions::class;
}
