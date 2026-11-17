<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\MediaPermissions;

class MediaPolicy extends ResourcePolicy
{
    protected static string $permissions = MediaPermissions::class;
}
