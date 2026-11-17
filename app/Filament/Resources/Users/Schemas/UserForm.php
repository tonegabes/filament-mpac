<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AuthMode;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use ToneGabes\BetterOptions\Forms\Components\CheckboxCards;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados Pessoais')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->maxLength(255)
                            ->required(),

                        self::getUsernameComponent(),
                        self::getEmailComponent(),

                        ...self::getPasswordComponents(),

                        ToggleButtons::make('is_active')
                            ->label('Ativo')
                            ->boolean()
                            ->inline()
                            ->disabled(fn (?User $record): bool => $record !== null && Auth::user()?->is($record))
                            ->dehydrated(fn (?User $record): bool => $record === null || ! Auth::user()?->is($record))
                            ->required(),
                    ]),

                Section::make('Perfis')
                    ->columnSpanFull()
                    ->description('Selecione os perfis associados a esse usuário.')
                    ->schema([
                        CheckboxCards::make('roles')
                            ->hiddenLabel()
                            ->bulkToggleable()
                            ->columns(3)
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): void {
                                    $actor = Auth::user();

                                    if (! $actor instanceof User || $actor->isSuperAdmin()) {
                                        return;
                                    }

                                    $allowedRoleIds = Role::with('permissions')
                                        ->get()
                                        ->filter(fn (Role $role): bool => $actor->canAssignRole($role))
                                        ->pluck('id');

                                    $query->whereIn('id', $allowedRoleIds);
                                },
                            )
                            ->rules([
                                fn (?User $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    $roleIds = is_array($value) ? array_map('intval', $value) : [];
                                    $actor = Auth::user();

                                    if ($actor instanceof User && ! $actor->isSuperAdmin()) {
                                        $selectedRoles = Role::with('permissions')->whereIn('id', $roleIds)->get();

                                        foreach ($selectedRoles as $role) {
                                            if (! $actor->canAssignRole($role)) {
                                                $fail('Você não tem permissão para atribuir o perfil selecionado.');

                                                return;
                                            }
                                        }
                                    }

                                    if ($record instanceof User && $record->isLastDeveloper()) {
                                        $developerRole = Role::where('name', UserRole::Developer->value)->first();

                                        if ($developerRole !== null && ! in_array($developerRole->id, $roleIds, true)) {
                                            $fail('Não é possível remover o perfil Desenvolvedor do último desenvolvedor do sistema.');
                                        }
                                    }
                                },
                            ])
                            ->required(),
                    ]),

            ])->columns(2);
    }

    /**
     * Password field, only available when authentication is local.
     *
     * @return list<TextInput>
     */
    private static function getPasswordComponents(): array
    {
        if (config('auth.mode') !== AuthMode::Local->value) {
            return [];
        }

        return [
            TextInput::make('password')
                ->label('Senha')
                ->password()
                ->revealable()
                ->minLength(8)
                ->maxLength(255)
                ->autocomplete('new-password')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText(fn (string $operation): ?string => $operation === 'edit'
                    ? 'Deixe em branco para manter a senha atual.'
                    : null),
        ];
    }

    private static function getUsernameComponent(): TextInput
    {
        $usernameComponent = TextInput::make('username')
            ->label('Nome de Usuário')
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->required();

        if (config('auth.mode') === AuthMode::Ldap->value) {
            $usernameComponent
                ->live()
                ->debounce(500)
                ->afterStateUpdated(function (Set $set, ?string $state): void {
                    $set('email', ($state ?? '') . config('auth.ldap.email_domain'));
                });
        }

        return $usernameComponent;
    }

    private static function getEmailComponent(): TextInput
    {
        $emailComponent = TextInput::make('email');

        if (config('auth.mode') === AuthMode::Ldap->value) {
            $emailComponent
                ->hint(config('auth.ldap.email_domain'))
                ->helperText('O email será gerado automaticamente com base no nome de usuário.')
                ->readonly();
        } else {
            $emailComponent
                ->email()
                ->maxLength(255)
                ->required();
        }

        return $emailComponent;
    }
}
