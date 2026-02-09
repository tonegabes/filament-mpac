# Settings

Este documento explica como usar o sistema de Settings (Spatie Laravel Settings) para gerenciar configurações do sistema.

## 📚 O que são Settings?

Settings são configurações persistentes do sistema que podem ser gerenciadas através da interface administrativa. Usamos **Spatie Laravel Settings** para isso.

## 🏗️ Estrutura

```
app/Settings/
└── SystemSettings.php
```

## 📝 Criando uma Classe de Settings

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public string $app_name = 'Minha Aplicação';
    public bool $maintenance_mode = false;
    public ?string $contact_email = null;

    /**
     * Retorna o nome do grupo de settings.
     */
    public static function group(): string
    {
        return 'app';
    }
}
```

### Exemplo Real: SystemSettings

```php
// app/Settings/SystemSettings.php
class SystemSettings extends Settings
{
    public const LOGO_DIRECTORY = 'logos';

    public string $app_name = 'MPAC';
    public bool $show_name_in_topbar = true;
    public bool $show_logo_in_topbar = true;
    public ?string $app_logo_light = null;
    public ?string $app_logo_dark = null;
    public ?string $auth_page_layout = null;
    public ?string $auth_page_background = null;
    public bool $enable_registration = false;
    public string $footer_text = 'Ministério Público do Estado do Acre';

    /**
     * Retorna o nome do grupo.
     */
    public static function group(): string
    {
        return 'system';
    }

    /**
     * Retorna a permissão para acessar a página de settings.
     */
    public static function getPermission(): BackedEnum
    {
        return SystemPermissions::SystemSettingsManage;
    }

    /**
     * Retorna a URL do logo claro.
     */
    public function getAppLogoLight(): string
    {
        return $this->app_logo_light 
            ? Storage::url($this->app_logo_light) 
            : asset('images/logo.png');
    }

    /**
     * Retorna a URL do logo escuro.
     */
    public function getAppLogoDark(): string
    {
        return $this->app_logo_dark 
            ? Storage::url($this->app_logo_dark) 
            : asset('images/logo-dark.png');
    }

    /**
     * Limpa o diretório de logos.
     */
    public static function cleanLogoDirectory(): void
    {
        $self = new self;
        $logos = [$self->app_logo_light, $self->app_logo_dark];
        $files = Storage::disk('public')->files(self::LOGO_DIRECTORY);
        $filesToDelete = array_diff($files, $logos);

        foreach ($filesToDelete as $file) {
            Storage::disk('public')->delete($file);
        }
    }
}
```

## ⚙️ Criando uma Página de Settings

### SettingsPage

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Settings\AppSettings;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class ManageApp extends SettingsPage
{
    protected static string $settings = AppSettings::class;

    protected static ?string $navigationLabel = 'Aplicação';

    protected ?string $heading = 'Configurações da Aplicação';

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
            ->components([
                Section::make('Geral')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nome da Aplicação')
                            ->required(),

                        Toggle::make('maintenance_mode')
                            ->label('Modo de Manutenção')
                            ->helperText('Quando ativado, apenas administradores podem acessar'),

                        TextInput::make('contact_email')
                            ->label('Email de Contato')
                            ->email(),
                    ]),
            ]);
    }
}
```

## 🔄 Usando Settings

### Acessando Settings

```php
use App\Settings\SystemSettings;

$settings = app(SystemSettings::class);

echo $settings->app_name;
echo $settings->getAppLogoLight();
```

### Atualizando Settings

```php
$settings = app(SystemSettings::class);
$settings->app_name = 'Novo Nome';
$settings->save();
```

### Em Blade Templates

```blade
{{ app(\App\Settings\SystemSettings::class)->app_name }}
```

## 🎯 Hooks e Callbacks

### afterSave

Executa após salvar as configurações:

```php
public function afterSave(): void
{
    SystemSettings::cleanLogoDirectory();
    
    Notification::make()
        ->title('Configurações salvas')
        ->success()
        ->send();
}
```

### mutateFormDataBeforeSave

Modifica dados antes de salvar:

```php
protected function mutateFormDataBeforeSave(array $data): array
{
    // Processa dados antes de salvar
    $data['app_name'] = trim($data['app_name']);
    
    return $data;
}
```

## 📋 Tipos de Propriedades

### Tipos Suportados

- `string`
- `int`
- `float`
- `bool`
- `array`
- Tipos nullable (`?string`, `?int`, etc.)

### Exemplo com Array

```php
class AppSettings extends Settings
{
    public array $allowed_ips = [];

    public static function group(): string
    {
        return 'app';
    }
}
```

## 🎯 Boas Práticas

1. **Grupos**: Use grupos para organizar settings relacionados
2. **Valores Padrão**: Sempre defina valores padrão
3. **Métodos Helper**: Crie métodos helper quando necessário
4. **Permissões**: Sempre verifique permissões em `canAccess()`
5. **Validação**: Valide dados no formulário
6. **Limpeza**: Limpe arquivos não utilizados quando apropriado

## 🔗 Próximos Passos

- [Páginas Customizadas](05-paginas-customizadas.md) - Veja como criar SettingsPage
- [Sistema de Permissões](07-sistema-permissoes.md) - Adicione controle de acesso
