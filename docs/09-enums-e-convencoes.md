# Enums e Convenções

Este documento lista os enums realmente utilizados no projeto e como aplicá-los.

## 📚 Enums atuais

```text
app/Enums/
├── AuthMode.php
├── FileCollection.php
├── NavGroups.php
├── PageLayouts.php
├── Panels.php
├── UserRole.php
└── Permissions/
    ├── DocumentPermissions.php
    ├── ImagePermissions.php
    ├── PanelPermissions.php
    ├── PermissionPermissions.php
    ├── RolePermissions.php
    ├── SystemPermissions.php
    ├── UserPermissions.php
    └── WildcardPermissions.php
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

Também define MIME types aceitos e `options()` para selects/filtros.

```php
FileCollection::Documents->disk();       // documents
FileCollection::SystemLogos->directory(); // system/logos
FileCollection::Images->acceptedMimeTypes();

// Em models com Spatie Media Library:
Document::fileCollection()->value; // 'documents'
Image::fileCollection()->disk();   // 'images'
```

O método nos models é `fileCollection()` (retorna `App\Enums\FileCollection`). Não use `mediaCollection()`.

Se o arquivo também importar `Spatie\MediaLibrary\MediaCollections\MediaCollection`, use alias para evitar colisão de nomes:

```php
use Spatie\MediaLibrary\MediaCollections\MediaCollection as SpatieMediaCollection;
```

## 👥 UserRole

Roles atuais:

- `Developer` → `Desenvolvedor`
- `Admin` → `Administrador`
- `User` → `Usuário` (default via `UserRole::default()`)

Valores persistidos no banco estão em português.

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
- `DocumentPermissions`
- `ImagePermissions`
- `WildcardPermissions`

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
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Settings](11-settings.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
