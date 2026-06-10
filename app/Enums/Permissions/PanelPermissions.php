<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Enums\Panels;
use Filament\Panel;

enum PanelPermissions: string
{
    case All = 'panels.*';
    case ViewAdmin = 'panels.view.admin';
    case ViewApp = 'panels.view.app';

    /**
     * Resolve the permission required to access a Filament panel.
     */
    public static function fromPanel(?Panel $panel): ?self
    {
        if ($panel === null) {
            return null;
        }

        return match (Panels::tryFrom($panel->getId())) {
            Panels::Admin => self::ViewAdmin,
            Panels::App => self::ViewApp,
            null => null,
        };
    }
}
