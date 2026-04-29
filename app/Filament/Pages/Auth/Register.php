<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Enums\Roles;
use App\Filament\Pages\Auth\Concerns\UsesConfiguredAuthLayout;
use App\Models\User;
use Filament\Auth\Pages\Register as VendorRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends VendorRegister
{
    use UsesConfiguredAuthLayout;

    protected string $view = 'filament.pages.auth.register';

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
