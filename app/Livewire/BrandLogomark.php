<?php

namespace App\Livewire;

use App\Settings\SystemSettings;
use Illuminate\View\View;
use Livewire\Component;

class BrandLogomark extends Component
{
    public string $appName;

    public string $lightLogo;

    public string $darkLogo;

    public bool $showLogo;

    public bool $showName;

    /**
     * Initialize component properties.
     */
    public function mount(): void
    {
        $settings = app(SystemSettings::class);

        $this->appName = $settings->app_name;
        $this->lightLogo = $settings->getAppLogoLight();
        $this->darkLogo = $settings->getAppLogoDark();
        $this->showLogo = $settings->show_logo_in_topbar;
        $this->showName = $settings->show_name_in_topbar;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('livewire.brand-logomark');
    }
}
