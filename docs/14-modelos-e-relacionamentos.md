# Modelos e Relacionamentos

Este documento explica as convenções de Models, relacionamentos Eloquent, Media Library e Activity Log.

## 📚 Convenções de Models

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
```

### Exemplo Real: User

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
```

## 🔗 Relacionamentos Eloquent

### BelongsTo

```php
// Product.php
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}

// Uso
$product->category;
$product->category_id;
```

### HasMany

```php
// Category.php
public function products(): HasMany
{
    return $this->hasMany(Product::class);
}

// Uso
$category->products;
```

### BelongsToMany

```php
// User.php
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class);
}

// Uso
$user->roles;
$user->roles()->attach($roleId);
$user->roles()->detach($roleId);
$user->roles()->sync([$roleId1, $roleId2]);
```

### HasOne

```php
// User.php
public function profile(): HasOne
{
    return $this->hasOne(Profile::class);
}

// Uso
$user->profile;
```

## 📁 Spatie Media Library

### Configuração Básica

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const COLLECTION_NAME = 'products';

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->useDisk('public');
    }
}
```

### Exemplo Real: Image

```php
// app/Models/Image.php
class Image extends Model implements HasFileUrl, HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    public const COLLECTION_NAME = 'images';

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(self::COLLECTION_NAME);
    }

    public static function getMimeTypeMap(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];
    }

    public function getFileUrl(): string
    {
        return $this->getFirstMediaUrl(self::COLLECTION_NAME);
    }

    public function getFilename(): string
    {
        return $this->getFirstMedia(self::COLLECTION_NAME)->file_name ?? '';
    }
}
```

### Conversões de Mídia

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('images')
        ->acceptsMimeTypes(['image/jpeg', 'image/png']);

    $this->addMediaConversion('thumb')
        ->width(368)
        ->height(232)
        ->sharpen(10);
}
```

### Uso em Formulários

```php
// ProductForm.php
SpatieMediaLibraryFileUpload::make('image')
    ->label('Imagem')
    ->collection(Product::COLLECTION_NAME)
    ->image()
    ->imageEditor()
    ->required();
```

## 📝 Activity Log

### Configuração Básica

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### Exemplo Real: User

```php
// app/Models/User.php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly($this->fillable)
        ->logOnlyDirty();
}
```

### Logging Customizado

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email'])
        ->logOnlyDirty()
        ->setDescriptionForEvent(fn(string $eventName) => "Product {$eventName}");
}
```

## 🎯 Traits em Models

### HasActiveScope

```php
use App\Traits\HasActiveScope;

class Product extends Model
{
    use HasActiveScope;

    // Métodos disponíveis:
    // - scopeIsActive()
    // - activate()
    // - deactivate()
    // - isActive()
    // - toggleActive()
}
```

Veja [Traits](10-traits.md) para mais detalhes.

## 🔍 Scopes

### Global Scope

```php
protected static function booted(): void
{
    static::addGlobalScope('active', function (Builder $builder) {
        $builder->where('is_active', true);
    });
}
```

### Local Scope

```php
public function scopePublished(Builder $query): Builder
{
    return $query->where('is_published', true)
        ->where('published_at', '<=', now());
}

// Uso
Product::published()->get();
```

## 📋 Accessors e Mutators

### Accessor

```php
public function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}

// Uso
$user->full_name;
```

### Mutator

```php
public function setEmailAttribute(string $value): void
{
    $this->attributes['email'] = strtolower($value);
}

// Uso
$user->email = 'TEST@EXAMPLE.COM'; // Salva como 'test@example.com'
```

## 🎯 Boas Práticas

1. **Type Hints**: Use type hints explícitos em relacionamentos
2. **Fillable**: Defina `$fillable` ou `$guarded`
3. **Casts**: Use método `casts()` em vez de propriedade `$casts`
4. **Relacionamentos**: Defina relacionamentos com type hints
5. **Media Library**: Use constantes para nomes de coleções
6. **Activity Log**: Configure logging apropriado
7. **Traits**: Use traits para funcionalidades reutilizáveis

## 🔗 Próximos Passos

- [Traits](10-traits.md) - Veja traits disponíveis
- [Schemas e Formulários](03-schemas-e-formularios.md) - Use relacionamentos em formulários
- [Criando Recursos Filament](02-criando-recursos-filament.md) - Crie Resources para models
