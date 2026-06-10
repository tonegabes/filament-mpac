<?php

declare(strict_types=1);

namespace App\Filament\Components\Navigation;

use App\Enums\Panels;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;

final class PanelSwitcher
{
    /**
     * Build user menu items for switching between accessible panels.
     *
     * @return Action[]
     */
    public static function userMenuItems(): array
    {
        return collect(Panels::cases())
            ->map(fn (Panels $panel): Action => self::toAction($panel))
            ->all();
    }

    /**
     * Convert a panel enum into a user menu action.
     */
    private static function toAction(Panels $panel): Action
    {
        return Action::make('switch_to_' . $panel->value)
            ->label('Painel ' . $panel->label())
            ->icon($panel->icon())
            ->url(fn (): string => self::panelUrl($panel))
            ->visible(fn (): bool => self::canSwitchTo($panel));
    }

    /**
     * Resolve the destination URL for a panel.
     */
    private static function panelUrl(Panels $panel): string
    {
        if ($panel->path() === '') {
            return url('/');
        }

        return url($panel->path());
    }

    /**
     * Check if the authenticated user can switch to the target panel.
     */
    private static function canSwitchTo(Panels $targetPanel): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        $currentPanel = Filament::getCurrentPanel();

        if ($currentPanel?->getId() === $targetPanel->value) {
            return false;
        }

        $targetFilamentPanel = Filament::getPanel($targetPanel->value);

        return $user->canAccessPanel($targetFilamentPanel);
    }
}
