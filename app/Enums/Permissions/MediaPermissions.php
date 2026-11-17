<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\ResourcePermissions;
use App\Traits\ResolvesResourcePermissions;

enum MediaPermissions: string implements ResourcePermissions
{
    use ResolvesResourcePermissions;
    case All = 'media.*';
    case ViewAny = 'media.view.any';
    case View = 'media.view';
    case Create = 'media.create';
    case Update = 'media.update';
    case Delete = 'media.delete';
    case Restore = 'media.restore';
    case ForceDelete = 'media.force-delete';
    case Replicate = 'media.replicate';
    case Reorder = 'media.reorder';
}
