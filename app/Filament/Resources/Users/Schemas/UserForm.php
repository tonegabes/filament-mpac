<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Roles;
use App\Filament\Components\Forms\RadioCards;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('username')
                            ->maxLength(255)
                            ->required(),

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
                        // CheckboxList::make('roles')
                        //     ->hiddenLabel()
                        //     ->relationship('roles', 'name')
                        //     ->required(),

                        RadioCards::make('role')
                            ->label('Obrigatório')
                            ->options(Roles::class)
                            ->descriptions([
                                Roles::Developer->value => Roles::Developer->description(),
                                Roles::Admin->value     => Roles::Admin->description(),
                                Roles::Operator->value  => Roles::Operator->description(),
                                Roles::Guest->value     => Roles::Guest->description(),
                            ])
                            ->required(),
                    ]),

            ])->columns(2);
    }
}
