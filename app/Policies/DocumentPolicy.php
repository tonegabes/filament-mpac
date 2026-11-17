<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\DocumentPermissions;

class DocumentPolicy extends ResourcePolicy
{
    protected static string $permissions = DocumentPermissions::class;
}
