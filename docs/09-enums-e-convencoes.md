# Enums e Convenções

Este documento lista os enums realmente utilizados no projeto e como aplicá-los.

## 📚 Enums atuais

```text
app/Enums/
├── AuthMode.php
├── FileCollection.php
├── NavGroups.php
├── PageLayouts.php
├── Roles.php
└── Permissions/
    ├── UserPermissions.php
    ├── PanelPermissions.php
    ├── RolePermissions.php
    ├── PermissionPermissions.php
    └── SystemPermissions.php
```

## 🧭 NavGroups

Usado para agrupar navegação do painel:

- `Authorization`
- `Tools`
- `Settings`
- `Files`

Exemplo:

```php
public static function getNavigationGroup(): string
{
    return NavGroups::Files->value;
}
```

## 🔐 AuthMode

Define o modo de autenticação:

- `AuthMode::Local`
- `AuthMode::Ldap`

Uso principal:

- `config/auth.php` (`auth.mode`)
- `AuthModeHandlerResolver`
- `AdminPanelProvider::configureRegistration()`

```php
$authMode = AuthMode::fromConfig(Config::string('auth.mode'));
```

## 📁 FileCollection

Enum central para coleções de arquivos, disco e diretório:

- `Images`
- `Documents`
- `SystemLogos`
- `SystemBackgrounds`

Também define MIME types aceitos para cada coleção.

```php
FileCollection::Documents->disk(); // documents
FileCollection::SystemLogos->directory(); // system/logos
```

## 👥 Roles

Roles atuais:

- `Developer`
- `Admin`

Valores persistidos no banco estão em português (`Desenvolvedor`, `Administrador`, etc.).

## 🎨 PageLayouts

Controla layout das páginas de auth:

- `Split`
- `Centered`
- `FullPage`

Usado em `SystemSettings` e na página `ManageSystem`.

## 🛡️ Permission Enums

Enums de permissões:

- `PanelPermissions`
- `UserPermissions`
- `RolePermissions`
- `PermissionPermissions`
- `SystemPermissions`

Cada enum é usado por policies, seeders e checks de acesso no painel.

## 🔧 BetterEnum

O projeto possui trait `App\Traits\BetterEnum` para utilitários em enums.

Use quando precisar expor listas para selects, filtros ou serialização de opções.

## 🎯 Convenções

1. Um enum por arquivo.
2. Cases em `PascalCase`.
3. Valores estáveis para persistência no banco/config.
4. Evitar strings mágicas quando um enum já existe.
5. Para novas coleções de arquivo, evoluir `FileCollection` antes de alterar forms/models.

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Settings](11-settings.md)
- [Panel Provider](15-panel-provider.md)
