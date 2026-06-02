<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum WildcardPermissions: string
{
    case All = '*';
    case ViewAny = '*.view.any';
    case UpdateOwn = '*.update.own';
}
