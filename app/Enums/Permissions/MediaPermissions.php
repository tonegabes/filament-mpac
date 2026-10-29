<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum MediaPermissions: string
{
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
