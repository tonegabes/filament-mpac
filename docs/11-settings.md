# Settings

Este documento descreve o uso de `Spatie Laravel Settings` no estado atual do projeto.

## 📚 Classe de Settings Ativa

Atualmente o projeto usa uma classe principal:

- `App\Settings\SystemSettings`

Ela controla branding, layouts de autenticação, logos/fundos e habilitação de registro.

## 🧱 Estrutura Atual

```php
// app/Settings/SystemSettings.php
class SystemSettings extends Settings
{
    public const LOGO_DIRECTORY = 'system/logos';
    public const BACKGROUNDS_DIRECTORY = 'system/backgrounds';

    public ?string $app_name = self::DEFAULT_APP_NAME;
    public ?string $app_sigla = self::DEFAULT_APP_SIGLA;
    public bool $show_app_logo = true;
    public ?string $app_logo_light = null;
    public ?string $app_logo_dark = null;
    public ?PageLayouts $auth_page_layout = self::DEFAULT_AUTH_PAGE_LAYOUT;
    public ?string $auth_page_background = null;
    public bool $enable_registration = true;
}
```

## 🔐 Controle de Acesso

`SystemSettings::getPermission()` retorna:

- `SystemPermissions::SystemSettingsManage`

E a página `ManageSystem` faz o gate com:

```php
public static function canAccess(): bool
{
    return Auth::user()?->can(SystemPermissions::SystemSettingsManage) ?? false;
}
```

## 🖼️ Logos e Fundos

Uploads de sistema são integrados com `FileCollection`:

- `FileCollection::SystemLogos` (`public`, diretório `system/logos`)
- `FileCollection::SystemBackgrounds` (`public`, diretório `system/backgrounds`)

Na página `ManageSystem`, os campos usam `LibraryFileUpload::publicImage(...)`.

Após salvar, o projeto remove arquivos órfãos:

```php
public function afterSave(): void
{
    $settings = app(SystemSettings::class);

    SystemSettings::cleanLogoDirectory($settings);
    SystemSettings::cleanBackgroundsDirectory($settings);
}
```

## 🧭 Layout de Login

O layout é persistido por `auth_page_layout` e usa o enum `PageLayouts`:

- `Split`
- `Centered`
- `FullPage`

As páginas de auth customizadas consomem esse valor via trait `UsesConfiguredAuthLayout`.

## 👤 Registro de Usuário

`enable_registration` controla se auto-registro fica disponível no painel.

Importante: o registro só é habilitado quando:

1. `auth.mode` está em modo local.
2. `enable_registration` está ativo.

## 🎯 Boas Práticas

1. Mantenha defaults seguros em `SystemSettings`.
2. Evite strings mágicas para coleção/disco de uploads; use `FileCollection`.
3. Sempre limpe arquivos não referenciados após atualização de settings.
4. Se mudar layout de auth, valide também as views em `resources/views/layouts/auth/`.
5. Evite adicionar settings sem migração correspondente em `database/settings/`.

## 🔗 Próximos Passos

- [Páginas Customizadas](05-paginas-customizadas.md)
- [Enums e Convenções](09-enums-e-convencoes.md)
- [Panel Provider](15-panel-provider.md)
