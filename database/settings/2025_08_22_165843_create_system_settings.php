<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('system.app_name', 'Ministério Público do Estado do Acre');
        $this->migrator->add('system.app_sigla', 'MPAC');

        $this->migrator->add('system.app_logo_light', null);
        $this->migrator->add('system.app_logo_dark', null);
        $this->migrator->add('system.show_app_logo', true);

        $this->migrator->add('system.enable_registration', true);

        $this->migrator->add('system.auth_page_layout', null);
        $this->migrator->add('system.auth_page_background', null);
        $this->migrator->add('system.footer_text', 'Ministério Público do Estado do Acre');
    }
};
