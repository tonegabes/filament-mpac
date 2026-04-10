<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum SystemPermissions: string
{
    case PanelsAll = 'system.panels';
    case PanelsViewAdmin = 'system.panels.view.admin';
    case PanelsViewOperator = 'system.panels.view.operator';
    case LogViewerAccess = 'system.log-viewer.access';
    case SystemSettingsManage = 'system.settings.manage';
}
