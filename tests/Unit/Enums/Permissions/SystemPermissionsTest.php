<?php

declare(strict_types=1);

use App\Enums\Permissions\SystemPermissions;

it('has expected cases and string values', function (): void {
    expect(SystemPermissions::PanelsAll->value)->toBe('system.panels')
        ->and(SystemPermissions::PanelsViewAdmin->value)->toBe('system.panels.view.admin')
        ->and(SystemPermissions::PanelsViewOperator->value)->toBe('system.panels.view.operator')
        ->and(SystemPermissions::LogViewerAccess->value)->toBe('system.log-viewer.access')
        ->and(SystemPermissions::SystemSettingsManage->value)->toBe('system.settings.manage')
        ->and(SystemPermissions::ViewActivityLog->value)->toBe('system.activity-log.view');
});

it('has exactly six cases', function (): void {
    expect(SystemPermissions::cases())->toHaveCount(6);
});
