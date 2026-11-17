<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AuthMode;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (config('auth.mode') === AuthMode::Ldap->value && is_string($data['username'] ?? null)) {
            $data['email'] = $data['username'] . config('auth.ldap.email_domain');
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
