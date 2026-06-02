<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum DocumentPermissions: string
{
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
