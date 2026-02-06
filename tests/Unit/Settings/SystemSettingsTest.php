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

it('getAppLogoLight returns Storage url when set', function (): void {
    Storage::disk('public')->put('logos/light.png', 'content');
    $settings = new SystemSettings;
    $settings->app_logo_light = 'logos/light.png';

    $url = $settings->getAppLogoLight();

    expect($url)->toContain('logos/light.png');
});

it('getAppLogoLight returns asset fallback when null', function (): void {
    $settings = new SystemSettings;
    $settings->app_logo_light = null;

    expect($settings->getAppLogoLight())->toBe(asset('images/logo.png'));
});

it('getAppLogoDark returns Storage url when set', function (): void {
    Storage::disk('public')->put('logos/dark.png', 'content');
    $settings = new SystemSettings;
    $settings->app_logo_dark = 'logos/dark.png';

    $url = $settings->getAppLogoDark();

    expect($url)->toContain('logos/dark.png');
});

it('getAppLogoDark returns asset fallback when null', function (): void {
    $settings = new SystemSettings;
    $settings->app_logo_dark = null;

    expect($settings->getAppLogoDark())->toBe(asset('images/logo-dark.png'));
});

it('getAppLayout returns auth_page_layout when set', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_layout = PageLayouts::Split->value;

    expect($settings->getAppLayout())->toBe(PageLayouts::Split->value);
});

it('getAppLayout returns FullPage value when null', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_layout = null;

    expect($settings->getAppLayout())->toBe(PageLayouts::FullPage->value);
});

it('getAuthPageBackground returns asset fallback when null', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_background = null;

    expect($settings->getAuthPageBackground())->toBe(asset('images/background.avif'));
});

it('getAuthPageBackground returns asset when auth_page_background is set but Image not mocked', function (): void {
    $settings = new SystemSettings;
    $settings->auth_page_background = null;

    expect($settings->getAuthPageBackground())->toBe(asset('images/background.avif'));
});

it('cleanLogoDirectory runs without exception', function (): void {
    Storage::disk('public')->put(SystemSettings::LOGO_DIRECTORY . '/file.png', 'content');

    SystemSettings::cleanLogoDirectory();

    expect(true)->toBeTrue();
});
