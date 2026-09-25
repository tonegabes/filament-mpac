<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum SystemPermissions: string
{
    case All = 'system.*';
    case SystemSettingsManage = 'system.settings.manage';
    case PulseAccess = 'system.pulse.access';
}
