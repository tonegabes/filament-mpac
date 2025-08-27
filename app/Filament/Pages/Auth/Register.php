<?php

namespace App\Filament\Pages\Auth;

use App\Enums\Roles;
use App\Models\User;
use App\Settings\SystemSettings;
use Filament\Auth\Pages\Register as VendorRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends VendorRegister
{
    protected string $view = 'filament.pages.auth.register';

    /**
     * Override the default layout to use the custom layout.
     */
    public function getLayout(): string
    {
        return app(SystemSettings::class)->getAppLayout();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $data['username'] = $data['email'];
        $data['is_active'] = true;

        /** @var User $user */
        $user = $this->getUserModel()::create($data);
        $user->assignRole(Roles::Operator);

        return $user;
    }
}
