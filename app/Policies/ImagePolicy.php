<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\ImagePermissions;

class ImagePolicy extends ResourcePolicy
{
    protected static string $permissions = ImagePermissions::class;
}
