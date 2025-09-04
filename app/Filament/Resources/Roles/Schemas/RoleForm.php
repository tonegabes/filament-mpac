<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->maxLength(255)
                    ->required(),

                Section::make('Permissões')
                    ->description('Selecione as permissões associadas a esse perfil.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Permissões')
                            ->hiddenLabel()
                            ->relationship('permissions', 'name')
                            ->columns(4)
                            ->required()
                            ->bulkToggleable()
                            ->searchable(),
                    ]),
            ])->columns(1);
    }
}
