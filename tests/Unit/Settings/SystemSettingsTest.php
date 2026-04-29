<?php

declare(strict_types=1);

use App\Enums\PageLayouts;
use App\Enums\Permissions\SystemPermissions;
use App\Settings\SystemSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

it('returns system as group', function (): void {
    expect(SystemSettings::group())->toBe('system');
});

it('returns SystemSettingsManage permission', function (): void {
    expect(SystemSettings::getPermission())->toBe(SystemPermissions::SystemSettingsManage);
});

it('is registered in settings config', function (): void {
    expect(config('settings.settings'))->toContain(SystemSettings::class);
});

it('getAppLogoLight returns Storage url when set', function (): void {
    Storage::disk('public')->put(SystemSettings::LOGO_DIRECTORY . '/light.png', 'content');
    $settings = new SystemSettings;
    $settings->app_logo_light = SystemSettings::LOGO_DIRECTORY . '/light.png';

    $url = $settings->getAppLogoLight();

    expect($url)->toContain('system/logos/light.png');
});

it('getAppLogoLight returns asset fallback when null', function (): void {
    $settings = new SystemSettings;
    $settings->app_logo_light = null;

    expect($settings->getAppLogoLight())->toBe(asset('images/logo.png'));
});

it('getAppLogoDark returns Storage url when set', function (): void {
    Storage::disk('public')->put(SystemSettings::LOGO_DIRECTORY . '/dark.png', 'content');
    $settings = new SystemSettings;
    $settings->app_logo_dark = SystemSettings::LOGO_DIRECTORY . '/dark.png';

    $url = $settings->getAppLogoDark();

    expect($url)->toContain('system/logos/dark.png');
});

it('getAppLogoDark returns asset fallback when null', function (): void {
    $settings = new SystemSettings;
    $settings->app_logo_dark = null;

    expect($settings->getAppLogoDark())->toBe(asset('images/logo-dark.png'));
});

it('getAppLayout returns auth_page_layout when set', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_layout = PageLayouts::Split;

    expect($settings->getAppLayout())->toBe(PageLayouts::Split->value);
});

it('getAppLayout returns default auth layout value when null', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_layout = null;

    expect($settings->getAppLayout())->toBe(SystemSettings::DEFAULT_AUTH_PAGE_LAYOUT->value)
        ->and($settings->getAuthPageLayout())->toBe(SystemSettings::DEFAULT_AUTH_PAGE_LAYOUT->value);
});

it('getAuthPageBackground returns asset fallback when null', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_background = null;

    expect($settings->getAuthPageBackground())->toBe(asset('images/background.avif'));
});

it('getAuthPageBackground returns Storage url when path exists on public disk', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_background = SystemSettings::BACKGROUNDS_DIRECTORY . '/bg.png';
    Storage::disk('public')->put($settings->auth_page_background, 'content');

    expect($settings->getAuthPageBackground())->toContain('system/backgrounds/bg.png');
});

it('getAuthPageBackground returns asset fallback when path does not exist', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_background = SystemSettings::BACKGROUNDS_DIRECTORY . '/missing.png';

    expect($settings->getAuthPageBackground())->toBe(asset('images/background.avif'));
});

it('getFooterBrandName returns configured app name or default', function (): void {
    $settings = new SystemSettings;
    $settings->app_name = 'Portal MPAC';

    expect($settings->getFooterBrandName())->toBe('Portal MPAC');

    $settings->app_name = null;

    expect($settings->getFooterBrandName())->toBe(SystemSettings::DEFAULT_APP_NAME);
});

it('cleanLogoDirectory keeps configured logos and deletes orphan files', function (): void {
    $light = SystemSettings::LOGO_DIRECTORY . '/light.png';
    $dark = SystemSettings::LOGO_DIRECTORY . '/dark.png';
    $orphan = SystemSettings::LOGO_DIRECTORY . '/orphan.png';

    Storage::disk('public')->put($light, 'content');
    Storage::disk('public')->put($dark, 'content');
    Storage::disk('public')->put($orphan, 'content');

    $settings = new SystemSettings;
    $settings->app_logo_light = $light;
    $settings->app_logo_dark = $dark;

    SystemSettings::cleanLogoDirectory($settings);

    Storage::disk('public')->assertExists([$light, $dark]);
    Storage::disk('public')->assertMissing($orphan);
});

it('cleanBackgroundsDirectory keeps only current auth_page_background', function (): void {
    $keep = SystemSettings::BACKGROUNDS_DIRECTORY . '/keep.png';
    $delete = SystemSettings::BACKGROUNDS_DIRECTORY . '/delete.png';

    Storage::disk('public')->put($keep, 'content');
    Storage::disk('public')->put($delete, 'content');

    $settings = new SystemSettings;
    $settings->auth_page_background = $keep;

    SystemSettings::cleanBackgroundsDirectory($settings);

    Storage::disk('public')->assertExists($keep);
    Storage::disk('public')->assertMissing($delete);
});
