<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Settings\SystemSettings;
use Illuminate\View\View;
use Livewire\Component;

class BrandLogomark extends Component
{
    public ?string $appName;

    public ?string $appSigla;

    public string $lightLogo;

    public string $darkLogo;

    public bool $showAppLogo;

    /**
     * Load the current branding values from system settings.
     */
    public function mount(): void
    {
        $settings = app(SystemSettings::class);

        $this->appName = $settings->app_name;
        $this->appSigla = $settings->app_sigla;
        $this->lightLogo = $settings->getAppLogoLight();
        $this->darkLogo = $settings->getAppLogoDark();
        $this->showAppLogo = $settings->show_app_logo;
    }

    /**
     * Render the brand logomark.
     */
    public function render(): View
    {
        return view('livewire.brand-logomark');
    }
}
