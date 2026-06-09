<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum ImagePermissions: string
{
    case All = 'images.*';
    case ViewAny = 'images.view.any';
    case View = 'images.view';
    case Create = 'images.create';
    case Update = 'images.update';
    case Delete = 'images.delete';
    case Restore = 'images.restore';
    case ForceDelete = 'images.force-delete';
    case Replicate = 'images.replicate';
    case Reorder = 'images.reorder';
}
