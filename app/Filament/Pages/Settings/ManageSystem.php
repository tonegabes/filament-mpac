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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use ToneGabes\BetterOptions\Forms\Components\RadioList;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ManageSystem extends SettingsPage
{
    protected static string $settings = SystemSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::FadersHorizontal;

    protected static ?string $navigationLabel = 'Sistema';

    protected ?string $heading = 'Configurações do Sistema';

    /**
     * Get the navigation group for the settings page.
     */
    public static function getNavigationGroup(): string
    {
        return NavGroups::Settings->value;
    }

    /**
     * Determine whether the current user can access this settings page.
     */
    public static function canAccess(): bool
    {
        return Auth::user()?->can(SystemPermissions::SystemSettingsManage) ?? false;
    }

    /**
     * Build the system settings form.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Tabs')
                    ->activeTab(1)
                    ->tabs([
                        $this->generalTab(),
                        $this->registrationTab(),
                        $this->loginTab(),
                    ]),
            ]);
    }

    /**
     * Remove uploaded files that are no longer referenced after saving.
     */
    public function afterSave(): void
    {
        $settings = app(SystemSettings::class);

        SystemSettings::cleanLogoDirectory($settings);
        SystemSettings::cleanBackgroundsDirectory($settings);
    }

    /**
     * Build the general settings tab.
     */
    private function generalTab(): Tab
    {
        return Tab::make('Geral')
            ->schema([
                $this->brandFieldset(),
                $this->logosSection(),
            ]);
    }

    /**
     * Build the registration settings tab.
     */
    private function registrationTab(): Tab
    {
        return Tab::make('Registro')
            ->schema([
                Toggle::make('enable_registration')
                    ->label('Habilitar registro no sistema')
                    ->helperText('Se habilitado, os usuários poderão se registrar sem convite.')
                    ->onIcon(Phosphor::Check)
                    ->offIcon(Phosphor::X)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    /**
     * Build the login settings tab.
     */
    private function loginTab(): Tab
    {
        return Tab::make('Login')
            ->schema([
                RadioList::make('auth_page_layout')
                    ->label('Layout da página de login')
                    ->default(SystemSettings::DEFAULT_AUTH_PAGE_LAYOUT->value)
                    ->enum(PageLayouts::class)
                    ->hiddenExtraText()
                    ->required(),

                $this->backgroundUpload(),
            ]);
    }

    /**
     * Build the brand fieldset.
     */
    private function brandFieldset(): Fieldset
    {
        return Fieldset::make('Identidade')
            ->columns(2)
            ->schema([
                TextInput::make('app_sigla')
                    ->label('Sigla do sistema')
                    ->maxLength(10)
                    ->helperText('Deixe vazio para não exibir a sigla do sistema.'),

                TextInput::make('app_name')
                    ->label('Nome do sistema')
                    ->maxLength(255)
                    ->helperText('Deixe vazio para não exibir o nome do sistema.'),
            ]);
    }

    /**
     * Build the logo upload section.
     */
    private function logosSection(): Section
    {
        return Section::make('Logos')
            ->columns(2)
            ->schema([
                $this->logoUpload('app_logo_light', 'Modo claro'),
                $this->logoUpload('app_logo_dark', 'Modo escuro'),

                Toggle::make('show_app_logo')
                    ->label('Exibir logo do sistema')
                    ->onIcon(Phosphor::Check)
                    ->offIcon(Phosphor::X)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    /**
     * Build a logo upload field.
     */
    private function logoUpload(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->disk('public')
            ->directory(SystemSettings::LOGO_DIRECTORY)
            ->visibility('public')
            ->preserveFilenames()
            ->image()
            ->imageEditor()
            ->maxSize(2048)
            ->openable()
            ->downloadable();
    }

    /**
     * Build the auth background upload field.
     */
    private function backgroundUpload(): FileUpload
    {
        return FileUpload::make('auth_page_background')
            ->label('Imagem de fundo')
            ->disk('public')
            ->directory(SystemSettings::BACKGROUNDS_DIRECTORY)
            ->visibility('public')
            ->preserveFilenames()
            ->image()
            ->imageEditor()
            ->maxSize(5120)
            ->openable()
            ->downloadable();
    }
}
