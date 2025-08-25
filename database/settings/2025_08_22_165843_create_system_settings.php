<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('system.app_name', 'MPAC');
        $this->migrator->add('system.show_name_in_topbar', true);

        $this->migrator->add('system.app_logo_light', null);
        $this->migrator->add('system.app_logo_dark', null);
        $this->migrator->add('system.show_logo_in_topbar', true);

        $this->migrator->add('system.enable_registration', true);
    }
};
