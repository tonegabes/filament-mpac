<?php

declare(strict_types=1);

use App\Enums\Permissions\SystemPermissions;

it('has expected cases and string values', function (): void {
    expect(SystemPermissions::All->value)->toBe('system')
        ->and(SystemPermissions::LogViewerAccess->value)->toBe('system.log-viewer.access')
        ->and(SystemPermissions::SystemSettingsManage->value)->toBe('system.settings.manage');
});

it('has exactly three cases', function (): void {
    expect(SystemPermissions::cases())->toHaveCount(3);
});
