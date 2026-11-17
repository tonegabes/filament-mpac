# Modelos e Relacionamentos

Este documento descreve os modelos reais do projeto, com foco em arquivos, Media Library e Activity Log.

## 📚 Modelos Principais

O projeto atualmente trabalha com:

- `User`
- `Role`
- `Permission`
- `Document`
- `Image`
- `Spatie\MediaLibrary\MediaCollections\Models\Media` (usado no `MediaResource`)

## 👤 User

`User` implementa `FilamentUser` e usa `HasRoles`, `HasIsActiveScope`, `HasBlameable` e `HasActivity` (Spatie Activitylog).

```php
// app/Models/User.php
class User extends Authenticatable implements FilamentUser
{
    use HasActivity;
    use HasBlameable;
    use HasFactory;
    use HasIsActiveScope;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
    ];

    public function canAccessPanel(?Panel $panel): bool
    {
        if (! $this->is_active || $panel === null) {
            return false;
        }

        $panelPermission = PanelPermissions::fromPanel($panel);
        $permission = $panelPermission !== null
            ? $panelPermission->value
            : "panels.view.{$panel->getId()}";

        return $this->can($permission);
    }
}
```

Helpers de hierarquia (usados por `UserPolicy` e formulários): `isSuperAdmin()`, `canAssignRole()`, `canManageUser()`, `isLastDeveloper()`. Colunas `created_by` / `updated_by` vêm de `HasBlameable` — ver [Traits](10-traits.md).

## 📁 Biblioteca de Arquivos

O domínio de arquivos é dividido em:

- `Document`: documentos de escritório (PDF, Word, Excel, etc.)
- `Image`: imagens para uso na aplicação
- `Media`: visão agregada dos arquivos no `MediaResource`

### FileCollection

As coleções e discos são centralizados em `FileCollection`:

```php
enum FileCollection: string
{
    case Images = 'images';
    case Documents = 'documents';
    case SystemLogos = 'system_logos';
    case SystemBackgrounds = 'system_backgrounds';
}
```

Mapeamento atual:

- `Images` -> disco `images`
- `Documents` -> disco `documents`
- `SystemLogos` -> disco `public`, diretório `system/logos`
- `SystemBackgrounds` -> disco `public`, diretório `system/backgrounds`

Models com Spatie Media Library expõem a coleção via `fileCollection()`:

## 📄 Model Document

```php
// app/Models/Document.php
class Document extends Model implements HasFileUrl, HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name'];

    public static function fileCollection(): FileCollection
    {
        return FileCollection::Documents;
    }

    public function registerMediaCollections(): void
    {
        $collection = self::fileCollection();

        $this
            ->addMediaCollection($collection->value)
            ->acceptsMimeTypes($collection->acceptedMimeTypes())
            ->useDisk(FileCollection::Documents->disk());
    }
}
```

`Document::getMimeTypeMap()` ainda existe e delega para `FileCollection::Documents->acceptedMimeTypes()`, mas os forms usam `LibraryFileUpload` (que já lê o enum).

## 🖼️ Model Image

```php
// app/Models/Image.php
class Image extends Model implements HasFileUrl, HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name'];

    public static function fileCollection(): FileCollection
    {
        return FileCollection::Images;
    }

    public function registerMediaCollections(): void
    {
        $collection = self::fileCollection();

        $this
            ->addMediaCollection($collection->value)
            ->acceptsMimeTypes($collection->acceptedMimeTypes())
            ->useDisk($collection->disk());
    }
}
```

Não use constante `COLLECTION_NAME` — a fonte da verdade é `fileCollection()`.

## 🧩 Resource x Modelo

### DocumentResource

- Modelo: `App\Models\Document`
- Páginas registradas: `index` e `view`
- `infolist()` definido no Resource

### ImageResource

- Modelo: `App\Models\Image`
- Páginas registradas: `index` e `view`
- `infolist()` definido no Resource

### MediaResource

- Modelo: `Spatie\MediaLibrary\MediaCollections\Models\Media`
- Páginas registradas: `index` e `view`
- Rotas de `create` e `edit` estão comentadas por padrão

## 💾 Discos e Visibilidade

Configuração atual em `config/filesystems.php`:

- Disco `images`: `storage/app/private/images`, `visibility => private`
- Disco `documents`: `storage/app/private/documents`, `visibility => private`
- Disco `public`: logos/fundos do sistema (`system/logos`, `system/backgrounds`)

Arquivos de biblioteca **não** são servidos por URL pública de storage. Use as rotas autenticadas:

| Nome | Path | Controller |
| --- | --- | --- |
| `documents.download` | `/documents/{document}/download` | `FileDownloadController@document` |
| `images.download` | `/images/{image}/download` | `FileDownloadController@image` |
| `media.download` | `/media/{media}/download` | `FileDownloadController@media` |

Cada action autoriza `view` via Gate/policy e streama o media do disco. Teste: `tests/Feature/Http/Controllers/FileDownloadControllerTest.php`.

## 📝 Activity Log

`User`, `Document` e `Image` usam `spatie/laravel-activitylog` com `logOnly()` e `logOnlyDirty()`.

Exemplo (`Document` registra só `name`):

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name'])
        ->logOnlyDirty();
}
```

`User` registra `name`, `username`, `email`, `is_active` (com `dontLogEmptyChanges()`).

### Rótulos de subject

`App\Support\ActivityLog::subjectTypeLabel()` resolve o label assim:

1. Registro interno (`registerSubject` / mapa conhecido)
2. Model que implementa `App\Contracts\HasActivityLabel` → `activityLabel()`
3. Fallback: `class_basename($type)`

A interface existe para extensibilidade; implemente no model quando o basename não for legível na UI de auditoria.

## 🎯 Boas Práticas

1. Use `FileCollection` para evitar strings mágicas de coleção/disco.
2. Centralize regras de MIME type no enum/modelo.
3. Mantenha `create/edit` desabilitado no `MediaResource` enquanto o fluxo oficial for somente leitura.
4. Garanta que uploads públicos estejam em discos com URL configurada.
5. Prefira reaproveitar `LibraryFileUpload` nos formulários.

## 🔗 Próximos Passos

- [Schemas e Formulários](03-schemas-e-formularios.md) para uploads com Media Library
- [Settings](11-settings.md) para logos e fundos via `SystemSettings`
- [Panel Provider](15-panel-provider.md) para navegação do grupo Arquivos
