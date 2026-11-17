<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AuthMode;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (config('auth.mode') === AuthMode::Ldap->value && is_string($data['username'] ?? null)) {
            $data['email'] = $data['username'] . config('auth.ldap.email_domain');
        }

        return $data;
    }
}
