# Páginas Customizadas

Este documento explica como criar páginas customizadas no Filament, incluindo SettingsPage, páginas de autenticação e páginas de visualização.

## 📚 Tipos de Páginas

No Filament, existem vários tipos de páginas:

1. **SettingsPage**: Páginas de configurações do sistema
2. **Page**: Páginas customizadas gerais
3. **ViewRecord**: Páginas de visualização de registros
4. **Auth Pages**: Páginas de autenticação customizadas

## ⚙️ SettingsPage

SettingsPage é usada para criar páginas de configurações que salvam dados em classes Settings (Spatie Laravel Settings).

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Settings\SystemSettings;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class ManageSystem extends SettingsPage
{
    protected static string $settings = SystemSettings::class;

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
        return $schema->components([
            // Componentes do formulário
        ]);
    }
}
```

### Exemplo Real: ManageSystem

```php
// app/Filament/Pages/Settings/ManageSystem.php
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
                    ->schema([
                        Section::make('Nome')
                            ->schema([
                                TextInput::make('app_name')
                                    ->label('Nome do Sistema')
                                    ->maxLength(255)
                                    ->required(),

                                Toggle::make('show_name_in_topbar')
                                    ->label('Exibir na barra de navegação')
                                    ->onIcon(Phosphor::Check)
                                    ->offIcon(Phosphor::X)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public function afterSave(): void
    {
        SystemSettings::cleanLogoDirectory();
    }
}
```

### Hooks Disponíveis

```php
// Antes de salvar
protected function mutateFormDataBeforeSave(array $data): array
{
    // Modifica dados antes de salvar
    return $data;
}

// Depois de salvar
public function afterSave(): void
{
    // Executa após salvar
    Notification::make()
        ->title('Configurações salvas')
        ->success()
        ->send();
}
```

## 📄 Page (Página Customizada Geral)

Para criar uma página customizada que não seja de configurações:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::ChartBar;

    protected string $view = 'filament.pages.reports';

    protected static ?string $navigationLabel = 'Relatórios';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Tools->value;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportar')
                ->icon(Phosphor::Download),
        ];
    }
}
```

E a view correspondente:

```blade
{{-- resources/views/filament/pages/reports.blade.php --}}
<x-filament-panels::page>
    <div>
        <h2>Relatórios</h2>
        <!-- Conteúdo da página -->
    </div>
</x-filament-panels::page>
```

## 👁️ ViewRecord (Página de Visualização)

Para criar uma página de visualização de registro (somente leitura):

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
```

No padrão atual do projeto, o `infolist()` fica no `Resource` (ex.: `DocumentResource::infolist()`), e a página `ViewRecord` mantém apenas ações e customizações de comportamento.

### Infolist Schema

```php
// app/Filament/Resources/Products/Schemas/ProductInfolist.php
class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome'),

                        TextEntry::make('price')
                            ->label('Preço')
                            ->money('BRL'),

                        IconEntry::make('is_active')
                            ->label('Ativo')
                            ->boolean(),
                    ]),
            ]);
    }
}
```

## 🔐 Páginas de Autenticação

### Login Customizado

`app/Filament/Pages/Auth/Login.php` estende o login do Filament e resolve o modo via `AuthModeHandlerResolver`:

```php
class Login extends VendorLogin
{
    use UsesConfiguredAuthLayout;

    public function __construct()
    {
        $authMode = AuthMode::fromConfig(Config::string('auth.mode'));
        $this->authModeHandler = app(AuthModeHandlerResolver::class)->resolve($authMode);
        // Em modo LDAP, também resolve LdapAuthService / LdapUserService
    }

    public function authenticate(): ?LoginResponse
    {
        $this->rateLimit(5);
        $data = $this->form->getState();
        $this->authModeHandler->authenticate($this, $data);
        session()->regenerate();

        return app(LoginResponse::class);
    }
}
```

#### Fluxo por modo

| Modo (`AUTH_MODE`) | Handler | Campo de login | Comportamento |
| --- | --- | --- | --- |
| `local` | `LocalAuthModeHandler` | e-mail | `attemptLocalAuth()` + MFA se habilitado |
| `ldap` | `LdapAuthModeHandler` | username + suffix de domínio | busca LDAP, autentica, sincroniza usuário local |

Config relevante (`.env` / `config/auth.php`):

- `AUTH_MODE=local|ldap`
- `auth.default_role` → role atribuída a usuários LDAP sem papel
- `LDAP_AUTH_REQUIRES_LOCAL` → se `true`, exige usuário local ativo pré-existente
- `LDAP_AUTH_EMAIL_DOMAIN` → sufixo no login LDAP **e** no e-mail gerado em `UserForm` (admin create/edit)

O mesmo domínio aparece em três pontos: login (UI + `LdapAuthService::authenticate`), normalização de username no `Login`, e derivação de e-mail no `UserForm` quando `AUTH_MODE=ldap`. Detalhes do formulário: [Schemas e Formulários](03-schemas-e-formularios.md).

#### Labels e ícones

Customizações atuais na UI de login:

- senha com `prefixIcon(Phosphor::Lock)`
- checkbox “Lembrar meu acesso”
- botão “Entrar”

Layout visual vem de `UsesConfiguredAuthLayout` + `SystemSettings` (`PageLayouts`).

### Register Customizado

Registro local só é habilitado quando o modo de auth permite (`allowsLocalRegistration()`) e a configuração do painel libera registro.

```php
protected function handleRegistration(array $data): Model
{
    $data['username'] = $data['email'];
    $data['is_active'] = true;

    /** @var User $user */
    $user = $this->getUserModel()::create($data);
    $user->assignRole(UserRole::User);

    return $user;
}
```

### Reset Password

```php
// app/Filament/Pages/Auth/Password/ResetPasswordRequest.php
class ResetPasswordRequest extends RequestPasswordReset
{
    // Customizações
}

// app/Filament/Pages/Auth/Password/ResetPasswordAction.php
class ResetPasswordAction extends ResetPassword
{
    // Customizações
}
```

E registrar no Panel Provider:

```php
// app/Providers/Filament/AdminPanelProvider.php
$panel
    ->login(Login::class)
    // Registro e reset são aplicados somente se
    // allowsLocalRegistration() e enable_registration forem true
;
```

## 🎨 Customizando Views

### Criar View Customizada

```php
protected string $view = 'filament.pages.custom-page';
```

```blade
{{-- resources/views/filament/pages/custom-page.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">{{ $this->heading }}</h2>
        
        <div>
            <!-- Conteúdo customizado -->
        </div>
    </div>
</x-filament-panels::page>
```

## 🔄 Lifecycle Hooks

### SettingsPage

```php
// Antes de salvar
protected function mutateFormDataBeforeSave(array $data): array
{
    return $data;
}

// Depois de salvar
public function afterSave(): void
{
    // Executa após salvar
}
```

### ViewRecord

```php
// Ao montar o componente
public function mount(int | string $record): void
{
    parent::mount($record);
    // Lógica customizada
}

// Antes de excluir
protected function beforeDelete(): void
{
    // Validações antes de excluir
}
```

## 🎯 Boas Práticas

1. **SettingsPage**: Use para configurações do sistema
2. **ViewRecord**: Use para visualização somente leitura
3. **Page**: Use para páginas customizadas gerais
4. **Autorização**: Sempre verifique permissões com `canAccess()`
5. **Navigation Groups**: Use `NavGroups` enum
6. **Ícones**: Sempre use Phosphor Icons
7. **Labels**: Use labels descritivos em português

## 🔗 Próximos Passos

- [Settings](11-settings.md) - Entenda o sistema de Settings
- [Sistema de Permissões](07-sistema-permissoes.md) - Adicione controle de acesso
- [Componentes Customizados](06-componentes-customizados.md) - Crie componentes reutilizáveis
