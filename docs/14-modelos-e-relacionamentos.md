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

`User` implementa `FilamentUser`, usa `HasRoles`, `HasActiveScope` e `LogsActivity`.

```php
// app/Models/User.php
class User extends Authenticatable implements FilamentUser
{
    use HasActiveScope;
    use HasFactory;
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

## 📄 Model Document

```php
// app/Models/Document.php
class Document extends Model implements HasFileUrl, HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name'];

    public const COLLECTION_NAME = FileCollection::Documents->value;

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(FileCollection::Documents->disk());
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

    public const COLLECTION_NAME = FileCollection::Images->value;

    protected $fillable = ['name'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(FileCollection::Images->disk());
    }
}
```

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

- Disco `images`: `storage/app/public/images`, URL `/storage/images`
- Disco `documents`: `storage/app/public/documents`, URL `/storage/documents`
- Disco `public`: usado por logos/fundos do sistema

Quando o arquivo precisar de acesso público, mantenha `visibility => public`.

## 📝 Activity Log

`User`, `Document` e `Image` usam `spatie/laravel-activitylog` com `logOnly()` e `logOnlyDirty()`.

Exemplo:

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly($this->fillable)
        ->logOnlyDirty();
}
```

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
