<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum ActivityPermissions: string
{
    case All = 'activities.*';
    case ViewAny = 'activities.view.any';
    case View = 'activities.view';
}
