<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\Concerns;

use App\Settings\SystemSettings;

trait UsesConfiguredAuthLayout
{
    /**
     * Get the auth layout configured in system settings.
     */
    public function getLayout(): string
    {
        return app(SystemSettings::class)->getAuthPageLayout();
    }
}
