<?php

declare(strict_types=1);

namespace App\Settings;

use App\Enums\PageLayouts;
use App\Enums\Permissions\SystemPermissions;
use App\Models\Image;
use BackedEnum;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public const LOGO_DIRECTORY = 'logos';

    public ?string $app_name = 'Ministério Público do Estado do Acre';

    public ?string $app_sigla = 'MPAC';

    public bool $show_app_logo = true;

    public ?string $app_logo_light = null;

    public ?string $app_logo_dark = null;

    public ?string $auth_page_layout = null;

    public ?string $auth_page_background = null;

    public bool $enable_registration = false;

    public string $footer_text = 'Ministério Público do Estado do Acre';

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
        $logos = array_values(array_filter(
            [$self->app_logo_light, $self->app_logo_dark],
            static fn (?string $path): bool => $path !== null && $path !== ''
        ));

        $files = Storage::disk('public')->files(self::LOGO_DIRECTORY);
        $keep = array_flip($logos);

        foreach ($files as $file) {
            if (! is_string($file)) {
                continue;
            }

            if (! isset($keep[$file])) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    /**
     * Get the default page layout.
     */
    public function getAppLayout(): string
    {
        return $this->auth_page_layout ?? PageLayouts::FullPage->value;
    }

    /**
     * Get the auth page background.
     */
    public function getAuthPageBackground(): string
    {
        if (! $this->auth_page_background) {
            return asset('images/background.avif');
        }

        $image = Image::getMediaByName($this->auth_page_background);

        return $image ? $image->getUrl() : asset('images/background.avif');
    }
}
