<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\ResourcePermissions;
use App\Traits\ResolvesResourcePermissions;

enum ActivityPermissions: string implements ResourcePermissions
{
    use ResolvesResourcePermissions;
    case All = 'activities.*';
    case ViewAny = 'activities.view.any';
    case View = 'activities.view';
}
