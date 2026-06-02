<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum SystemPermissions: string
{
    case All = 'system.*';
    case LogViewerAccess = 'system.log-viewer.access';
    case SystemSettingsManage = 'system.settings.manage';
}
