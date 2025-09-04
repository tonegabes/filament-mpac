<?php

declare(strict_types=1);

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->maxWidth('md')
                    ->label('Nome'),

                Section::make('Perfis Associados')
                    ->description('Selecione os perfis associados a essa permissão.')
                    ->schema([
                        CheckboxList::make('roles')
                            ->hiddenLabel()
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),
            ])->columns(1);
    }
}
