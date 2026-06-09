<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum PermissionPermissions: string
{
    case All = 'permissions.*';
    case ViewAny = 'permissions.view.any';
    case View = 'permissions.view';
    case Create = 'permissions.create';
    case Update = 'permissions.update';
    case Delete = 'permissions.delete';
    case Restore = 'permissions.restore';
    case ForceDelete = 'permissions.force-delete';
    case Replicate = 'permissions.replicate';
    case Reorder = 'permissions.reorder';
}
