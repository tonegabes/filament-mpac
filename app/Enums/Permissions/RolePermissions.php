<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum RolePermissions: string
{
    case All = 'roles.*';
    case ViewAny = 'roles.view.any';
    case View = 'roles.view';
    case Create = 'roles.create';
    case Update = 'roles.update';
    case Delete = 'roles.delete';
    case Restore = 'roles.restore';
    case ForceDelete = 'roles.force-delete';
    case Replicate = 'roles.replicate';
    case Reorder = 'roles.reorder';
}
