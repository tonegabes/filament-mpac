<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
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
                        CheckboxCards::make('roles')
                            ->hiddenLabel()
                            ->bulkToggleable()
                            ->columns(3)
                            ->relationship('roles', 'name')
                            ->required(),
                    ]),

            ])->columns(2);
    }
}
