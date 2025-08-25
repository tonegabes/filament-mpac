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

    /**
     * Initialize component properties.
     */
    public function mount(): void
    {
        $this->appName = app(SystemSettings::class)->app_name;
        $this->lightLogo = 'images/logo.png';
        $this->darkLogo = 'images/logo-dark.png';
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('livewire.brand-logomark');
    }
}
