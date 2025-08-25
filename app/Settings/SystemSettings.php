<?php

declare(strict_types=1);

namespace App\Settings;

use App\Enums\Permissions\SystemPermissions;
use BackedEnum;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    const LOGO_DIRECTORY = 'logos';

    public string $app_name = 'MPAC';

    public bool $show_name_in_topbar = true;

    public bool $show_logo_in_topbar = true;

    public ?string $app_logo_light = null;

    public ?string $app_logo_dark = null;

    public bool $enable_registration = false;

    /**
     * Return the group name for the settings.
     */
    public static function group(): string
    {
        return 'system';
    }

    /**
     * Return the permission to access the settings page.
     */
    public static function getPermission(): BackedEnum
    {
        return SystemPermissions::SystemSettingsManage;
    }

    /**
     * Get the light logo URL.
     */
    public function getAppLogoLight(): string
    {
        return $this->app_logo_light ? Storage::url($this->app_logo_light) : asset('images/logo.png');
    }

    /**
     * Get the dark logo URL.
     */
    public function getAppLogoDark(): string
    {
        return $this->app_logo_dark ? Storage::url($this->app_logo_dark) : asset('images/logo-dark.png');
    }

    /**
     * Clean the logo directory.
     */
    public static function cleanLogoDirectory(): void
    {
        $self = new self;
        $logos = [$self->app_logo_light, $self->app_logo_dark];

        $files = Storage::disk('public')->files(self::LOGO_DIRECTORY);

        $filesToDelete = array_diff($files, $logos);

        foreach ($filesToDelete as $file) {
            Storage::disk('public')->delete($file);
        }
    }
}
