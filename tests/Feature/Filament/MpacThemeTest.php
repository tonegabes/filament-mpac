<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Support\Colors\Color;

it('registers the mpac vite theme on admin and app panels', function (): void {
    $themePath = 'resources/css/mpac-theme/index.css';

    expect(Filament::getPanel('admin')->getViteTheme())->toBe($themePath)
        ->and(Filament::getPanel('app')->getViteTheme())->toBe($themePath);
});

it('uses starter-derived panel tokens for font, width, and accent colors', function (): void {
    $admin = Filament::getPanel('admin');
    $app = Filament::getPanel('app');

    expect($admin->getFontFamily())->toBe('Inter')
        ->and($app->getFontFamily())->toBe('Inter')
        ->and($admin->getSidebarWidth())->toBe('15rem')
        ->and($admin->getColors()['primary'])->toBe('#7AB427')
        ->and($admin->getColors()['success'])->toBe('#7AB427')
        ->and($admin->getColors()['gray'])->toBe(Color::Zinc)
        ->and($app->getColors()['primary'])->toBe('#7AB427')
        ->and($app->getColors()['success'])->toBe('#7AB427')
        ->and($app->getColors()['gray'])->toBe(Color::Zinc);
});

it('ships starter hex tokens and layered nav/content shadows', function (): void {
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

    expect($themeCss)->toContain('--mpac-sidebar-bg: #e6e6ea')
        ->and($themeCss)->toContain('--mpac-sidebar-bg-soft: #f9f9fa')
        ->and($themeCss)->toContain('--mpac-accent: #b2e071')
        ->and($themeCss)->toContain('--mpac-accent-strong: #7ab427')
        ->and($themeCss)->toContain('--mpac-radius-nav: 0.75rem')
        ->and($themeCss)->toContain('--mpac-shadow-nav-active')
        ->and($themeCss)->toContain('0 11px 4px rgb(7 7 8 / 0.01)')
        ->and($themeCss)->toContain('--mpac-shadow-content-edge')
        ->and($themeCss)->toContain('filament/filament/resources/css/theme.css')
        ->and($sidebarCss)->toContain('fi-sidebar-item.fi-active')
        ->and($sidebarCss)->toContain('--mpac-shadow-nav-active')
        ->and($sidebarCss)->toContain('rounded-(--mpac-radius-nav)')
        ->and($layoutCss)->toContain('border-top-left-radius')
        ->and($layoutCss)->toContain('--mpac-shadow-content-edge');
});

it('includes the mpac theme in the vite input entrypoints', function (): void {
    $viteConfig = file_get_contents(base_path('vite.config.js'));

    expect($viteConfig)->toContain('resources/css/mpac-theme/index.css');
});
