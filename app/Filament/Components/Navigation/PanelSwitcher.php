<?php

declare(strict_types=1);

namespace App\Filament\Components\Navigation;

use App\Enums\Panels;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Panel;
use ToneGabes\Filament\Icons\Enums\Phosphor;

final class PanelSwitcher
{
    /**
     * Build user menu items for switching between accessible panels.
     *
     * @return Action[]
     */
    public static function userMenuItems(): array
    {
        return collect(Filament::getPanels())
            ->map(fn (Panel $panel): Action => self::toAction($panel))
            ->values()
            ->all();
    }

    /**
     * Convert a panel into a user menu action.
     */
    private static function toAction(Panel $panel): Action
    {
        $knownPanel = Panels::tryFrom($panel->getId());
        $label = $knownPanel?->label() ?? ucfirst($panel->getId());
        $icon = $knownPanel?->icon() ?? Phosphor::SquaresFour;

        return Action::make('switch_to_' . $panel->getId())
            ->label('Painel ' . $label)
            ->icon($icon)
            ->url(fn (): string => self::panelUrl($panel))
            ->visible(fn (): bool => self::canSwitchTo($panel));
    }

    /**
     * Resolve the destination URL for a panel.
     */
    private static function panelUrl(Panel $panel): string
    {
        $path = $panel->getPath();

        if ($path === '') {
            return url('/');
        }

        return url($path);
    }

    /**
     * Check if the authenticated user can switch to the target panel.
     */
    private static function canSwitchTo(Panel $targetPanel): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        $currentPanel = Filament::getCurrentPanel();

        if ($currentPanel?->getId() === $targetPanel->getId()) {
            return false;
        }

        return $user->canAccessPanel($targetPanel);
    }
}
