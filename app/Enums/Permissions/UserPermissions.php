<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\ResourcePermissions;
use App\Traits\ResolvesResourcePermissions;

enum UserPermissions: string implements ResourcePermissions
{
    use ResolvesResourcePermissions;
    case All = 'users.*';
    case ViewAny = 'users.view.any';
    case View = 'users.view';
    case Create = 'users.create';
    case Update = 'users.update';
    case Delete = 'users.delete';
    case Restore = 'users.restore';
    case ForceDelete = 'users.force-delete';
    case Replicate = 'users.replicate';
    case Reorder = 'users.reorder';
}
