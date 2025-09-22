<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Enums\NavGroups;
use App\Enums\PageLayouts;
use App\Enums\Permissions\SystemPermissions;
use App\Settings\SystemSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use ToneGabes\BetterOptions\Forms\Components\RadioList;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ManageSystem extends SettingsPage
{
    protected static string $settings = SystemSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::FadersHorizontal;

    protected static ?string $navigationLabel = 'Sistema';

    protected ?string $heading = 'Configurações do Sistema';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Settings->value;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can(SystemPermissions::SystemSettingsManage) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Fieldset::make()
                    ->columns(1)
                    ->extraAttributes([
                        'class' => 'border-none p-0',
                    ])
                    ->schema([
                        Section::make('Nome')
                            ->schema([
                                TextInput::make('app_name')
                                    ->label('Nome do Sistema')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->required(),

                                Toggle::make('show_name_in_topbar')
                                    ->label('Exibir na barra de navegação')
                                    ->onIcon(Phosphor::Check)
                                    ->offIcon(Phosphor::X)
                                    ->columnSpanFull()
                                    ->required(),
                            ]),

                        Section::make('Registro')->schema([
                            Toggle::make('enable_registration')
                                ->label('Habilitar Registro no Sistema')
                                ->helperText('Se habilitado, os usuários poderão se registrar no sistema.')
                                ->onIcon(Phosphor::Check)
                                ->offIcon(Phosphor::X)
                                ->columnSpanFull()
                                ->required(),
                        ]),

                        Section::make('Login')->schema([
                            RadioList::make('auth_page_layout')
                                ->label('Layout da página de login')
                                ->options([
                                    PageLayouts::Split->value    => PageLayouts::Split->getLabel(),
                                    PageLayouts::Centered->value => PageLayouts::Centered->getLabel(),
                                    PageLayouts::FullPage->value => PageLayouts::FullPage->getLabel(),
                                ])
                                ->descriptions([
                                    PageLayouts::Split->value    => PageLayouts::Split->getDescription(),
                                    PageLayouts::Centered->value => PageLayouts::Centered->getDescription(),
                                    PageLayouts::FullPage->value => PageLayouts::FullPage->getDescription(),
                                ])
                                ->icons([
                                    PageLayouts::Split->value    => PageLayouts::Split->getIcon(),
                                    PageLayouts::Centered->value => PageLayouts::Centered->getIcon(),
                                    PageLayouts::FullPage->value => PageLayouts::FullPage->getIcon(),
                                ])
                                ->required(),

                            FileUpload::make('auth_page_background')
                                ->label('Imagem de fundo')
                                ->disk('public')
                                ->directory(SystemSettings::LOGO_DIRECTORY)
                                ->preserveFilenames()
                                ->image()
                                ->imageEditor()
                                ->columnSpanFull()
                            ,
                        ]),
                    ]),

                Section::make('Logos')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('app_logo_light')
                            ->label('Modo Claro')
                            ->disk('public')
                            ->directory(SystemSettings::LOGO_DIRECTORY)
                            ->preserveFilenames()
                            ->image()
                            ->imageEditor(),

                        FileUpload::make('app_logo_dark')
                            ->label('Modo Escuro')
                            ->disk('public')
                            ->directory(SystemSettings::LOGO_DIRECTORY)
                            ->preserveFilenames()
                            ->image()
                            ->imageEditor(),

                        Toggle::make('show_logo_in_topbar')
                            ->label('Exibir na barra de navegação')
                            ->onIcon(Phosphor::Check)
                            ->offIcon(Phosphor::X)
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }

    public function afterSave(): void
    {
        SystemSettings::cleanLogoDirectory();
    }
}
