<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\ResourcePermissions;
use App\Traits\ResolvesResourcePermissions;

enum DocumentPermissions: string implements ResourcePermissions
{
    use ResolvesResourcePermissions;
    case All = 'documents.*';
    case ViewAny = 'documents.view.any';
    case View = 'documents.view';
    case Create = 'documents.create';
    case Update = 'documents.update';
    case Delete = 'documents.delete';
    case Restore = 'documents.restore';
    case ForceDelete = 'documents.force-delete';
    case Replicate = 'documents.replicate';
    case Reorder = 'documents.reorder';
}
