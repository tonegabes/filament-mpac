# Modelos e Relacionamentos

Este documento descreve os modelos reais do projeto, com foco em arquivos, Media Library e Activity Log.

## 📚 Modelos principais

- `User`
- `Role`
- `Permission`
- `Document`
- `Image`
- `Spatie\MediaLibrary\MediaCollections\Models\Media` (usado no `MediaResource`)

## 👤 User

`User` implementa `FilamentUser`, usa `HasRoles`, `HasIsActiveScope` e `LogsActivity`.

```php
// app/Models/User.php
class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasIsActiveScope;
    use HasRoles;
    use LogsActivity;
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
        $permission = PanelPermissions::fromPanel($panel);

        if ($permission === null) {
            return false;
        }

        return $this->can($permission);
    }
}
```

Filtro de usuários ativos: `User::query()->active()`.

## 📁 Biblioteca de arquivos

O domínio de arquivos é dividido em:

- `Document`: documentos de escritório (PDF, Word, Excel, etc.)
- `Image`: imagens para uso na aplicação
- `Media`: visão agregada dos arquivos no `MediaResource`

### FileCollection

Coleções, discos, diretórios e MIME types ficam em `App\Enums\FileCollection`:

| Case | Valor | Disco | Diretório |
| --- | --- | --- | --- |
| `Images` | `images` | `images` | — |
| `Documents` | `documents` | `documents` | — |
| `SystemLogos` | `system_logos` | `public` | `system/logos` |
| `SystemBackgrounds` | `system_backgrounds` | `public` | `system/backgrounds` |

### Padrão `fileCollection()`

Models com Spatie Media Library **não** usam mais constante `COLLECTION_NAME`. Cada model expõe:

```php
public static function fileCollection(): FileCollection
```

Use esse método em forms, tests, URLs e registro de coleções:

```php
Image::fileCollection()->value;              // 'images'
Image::fileCollection()->disk();             // 'images'
Image::fileCollection()->acceptedMimeTypes();
Document::fileCollection()->value;           // 'documents'
```

`Document` ainda mantém `getMimeTypeMap()` como atalho para MIME types de documentos; preferir `fileCollection()->acceptedMimeTypes()` em código novo.

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

    public function getFileUrl(): string
    {
        return $this->getFirstMediaUrl(self::fileCollection()->value);
    }
}
```

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

    public static function getMediaByName(string $name): ?Media
    {
        return Media::where([
            ['file_name', $name],
            ['collection_name', self::fileCollection()->value],
        ])->first();
    }
}
```

## 🧩 Resource × modelo

### DocumentResource

- Modelo: `App\Models\Document`
- Páginas: `index` e `view`
- Form usa `LibraryFileUpload::mediaLibrary(..., FileCollection::Documents, ...)`

### ImageResource

- Modelo: `App\Models\Image`
- Páginas: `index` e `view`
- Form usa `LibraryFileUpload::mediaLibrary('image', Image::fileCollection(), 'Imagem')`

### MediaResource

- Modelo: `Spatie\MediaLibrary\MediaCollections\Models\Media`
- Páginas: `index` e `view`
- `create` / `edit` desabilitados por padrão (somente leitura)

## 💾 Discos e visibilidade

Em `config/filesystems.php`:

- Disco `images`: `storage/app/public/images`, URL `/storage/images`
- Disco `documents`: `storage/app/public/documents`, URL `/storage/documents`
- Disco `public`: logos/fundos do sistema

Para acesso público, rode `php artisan storage:link` e mantenha `visibility => public` quando necessário.

## 📝 Activity Log

`User`, `Document` e `Image` usam `spatie/laravel-activitylog` com `logOnly()` + `logOnlyDirty()`.

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly($this->fillable)
        ->logOnlyDirty();
}
```

## 🎯 Boas práticas

1. Centralize coleção/disco/MIME em `FileCollection` + `fileCollection()`.
2. Prefira `LibraryFileUpload` nos forms de biblioteca.
3. Mantenha `MediaResource` somente leitura enquanto o fluxo oficial for via Document/Image.
4. Em testes, faça `Storage::fake(Image::fileCollection()->value)` (ou Documents).
5. Não reintroduza `COLLECTION_NAME` — quebra o padrão atual dos models.

## 🔗 Próximos Passos

- [Schemas e Formulários](03-schemas-e-formularios.md)
- [Traits](10-traits.md)
- [Settings](11-settings.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
