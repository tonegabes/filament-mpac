<?php

declare(strict_types=1);

namespace App\Settings;

use App\Enums\PageLayouts;
use App\Enums\Permissions\SystemPermissions;
use BackedEnum;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public const LOGO_DIRECTORY = 'system/logos';

    public const BACKGROUNDS_DIRECTORY = 'system/backgrounds';

    public const DEFAULT_APP_NAME = 'Ministério Público do Estado do Acre';

    public const DEFAULT_APP_SIGLA = 'MPAC';

    public const DEFAULT_LOGO_LIGHT = 'images/logo.png';

    public const DEFAULT_LOGO_DARK = 'images/logo-dark.png';

    public const DEFAULT_AUTH_BACKGROUND = 'images/background.avif';

    public const DEFAULT_AUTH_PAGE_LAYOUT = PageLayouts::Split;

    public ?string $app_name = self::DEFAULT_APP_NAME;

    public ?string $app_sigla = self::DEFAULT_APP_SIGLA;

    public bool $show_app_logo = true;

    public ?string $app_logo_light = null;

    public ?string $app_logo_dark = null;

    public ?PageLayouts $auth_page_layout = self::DEFAULT_AUTH_PAGE_LAYOUT;

    public ?string $auth_page_background = null;

    public bool $enable_registration = true;

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
        return $this->getPublicFileUrl($this->app_logo_light, self::DEFAULT_LOGO_LIGHT);
    }

    /**
     * Get the dark logo URL.
     */
    public function getAppLogoDark(): string
    {
        return $this->getPublicFileUrl($this->app_logo_dark, self::DEFAULT_LOGO_DARK);
    }

    /**
     * Clean the logo directory.
     */
    public static function cleanLogoDirectory(?self $settings = null): void
    {
        $settings ??= app(self::class);

        $logos = array_values(array_filter(
            [$settings->app_logo_light, $settings->app_logo_dark],
            static fn (?string $path): bool => $path !== null && $path !== ''
        ));

        self::deleteUnusedPublicFiles(self::LOGO_DIRECTORY, $logos);
    }

    /**
     * Get the default page layout.
     */
    public function getAppLayout(): string
    {
        return $this->getAuthPageLayout();
    }

    /**
     * Get the auth page layout view.
     */
    public function getAuthPageLayout(): string
    {
        return ($this->auth_page_layout ?? self::DEFAULT_AUTH_PAGE_LAYOUT)->value;
    }

    /**
     * Get the auth page background.
     */
    public function getAuthPageBackground(): string
    {
        return $this->getPublicFileUrl($this->auth_page_background, self::DEFAULT_AUTH_BACKGROUND);
    }

    /**
     * Get the name used in auth layout footers.
     */
    public function getFooterBrandName(): string
    {
        return $this->app_name ?: self::DEFAULT_APP_NAME;
    }

    /**
     * Clean the auth backgrounds directory.
     */
    public static function cleanBackgroundsDirectory(?self $settings = null): void
    {
        $settings ??= app(self::class);

        $keepPaths = array_filter(
            [$settings->auth_page_background],
            static fn (?string $path): bool => $path !== null && $path !== ''
        );

        self::deleteUnusedPublicFiles(self::BACKGROUNDS_DIRECTORY, $keepPaths);
    }

    /**
     * @param  list<string>  $keepPaths
     */
    private static function deleteUnusedPublicFiles(string $directory, array $keepPaths): void
    {
        $keep = array_flip($keepPaths);
        $disk = self::publicDisk();
        $files = $disk->files($directory);

        foreach ($files as $file) {
            if (! is_string($file)) {
                continue;
            }

            if (isset($keep[$file])) {
                continue;
            }

            $disk->delete($file);
        }
    }

    /**
     * Get a public file URL or a local asset fallback.
     */
    private function getPublicFileUrl(?string $path, string $fallbackAsset): string
    {
        if (! $path) {
            return asset($fallbackAsset);
        }

        $disk = self::publicDisk();

        if (! $disk->exists($path)) {
            return asset($fallbackAsset);
        }

        return $disk->url($path);
    }

    /**
     * Get the public filesystem disk.
     */
    private static function publicDisk(): FilesystemAdapter
    {
        return Storage::disk('public');
    }
}
