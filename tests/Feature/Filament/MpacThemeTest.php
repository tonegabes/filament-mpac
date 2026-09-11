<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Support\Colors\Color;

it('registers the mpac vite theme on admin and app panels', function (): void {
    $themePath = 'resources/css/mpac-theme/index.css';

    expect(Filament::getPanel('admin')->getViteTheme())->toBe($themePath)
        ->and(Filament::getPanel('app')->getViteTheme())->toBe($themePath);
});

it('uses cool mac-like panel tokens for font and accent colors', function (): void {
    $admin = Filament::getPanel('admin');
    $app = Filament::getPanel('app');

    expect($admin->getFontFamily())->toBe('Plus Jakarta Sans')
        ->and($app->getFontFamily())->toBe('Plus Jakarta Sans')
        ->and($admin->getColors()['primary'])->toBe(Color::Emerald)
        ->and($admin->getColors()['success'])->toBe(Color::Emerald)
        ->and($admin->getColors()['gray'])->toBe(Color::Slate)
        ->and($app->getColors()['primary'])->toBe(Color::Emerald)
        ->and($app->getColors()['success'])->toBe(Color::Emerald)
        ->and($app->getColors()['gray'])->toBe(Color::Slate);
});

it('ships the mpac theme stylesheet and component tokens', function (): void {
    $theme = resource_path('css/mpac-theme/index.css');
    $sidebar = resource_path('css/mpac-theme/components/sidebar.css');
    $layout = resource_path('css/mpac-theme/components/layout.css');
    $surfaces = resource_path('css/mpac-theme/components/surfaces.css');

    expect(file_exists($theme))->toBeTrue()
        ->and(file_exists($sidebar))->toBeTrue()
        ->and(file_exists($layout))->toBeTrue()
        ->and(file_exists($surfaces))->toBeTrue();

    $themeCss = file_get_contents($theme);
    $sidebarCss = file_get_contents($sidebar);
    $layoutCss = file_get_contents($layout);

    expect($themeCss)->toContain('--mpac-sidebar-bg')
        ->and($themeCss)->toContain('--mpac-radius-pill')
        ->and($themeCss)->toContain('--mpac-shadow-nav-active')
        ->and($themeCss)->toContain('filament/filament/resources/css/theme.css')
        ->and($sidebarCss)->toContain('fi-sidebar-item.fi-active')
        ->and($sidebarCss)->toContain('--mpac-shadow-nav-active')
        ->and($layoutCss)->toContain('border-top-left-radius')
        ->and($layoutCss)->toContain('--mpac-shadow-content-edge');
});

it('includes the mpac theme in the vite input entrypoints', function (): void {
    $viteConfig = file_get_contents(base_path('vite.config.js'));

    expect($viteConfig)->toContain('resources/css/mpac-theme/index.css');
});
