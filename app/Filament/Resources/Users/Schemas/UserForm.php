<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AuthMode;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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

                        ToggleButtons::make('is_active')
                            ->label('Ativo')
                            ->boolean()
                            ->inline()
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
                            ->relationship('roles', 'name')
                            ->required(),
                    ]),

            ])->columns(2);
    }

    private static function getUsernameComponent(): TextInput
    {
        $usernameComponent = TextInput::make('username')
            ->label('Nome de Usuário');

        if (config('auth.mode') === AuthMode::Ldap->value) {
            $usernameComponent
                ->live()
                ->debounce(500)
                ->afterStateUpdated(function (Set $set, $state) {
                    $set('email', $state . config('auth.ldap.email_domain'));
                });
        } else {
            $usernameComponent
                ->maxLength(255)
                ->required();
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
