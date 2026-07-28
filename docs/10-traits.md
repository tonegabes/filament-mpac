# Traits

Este documento explica os Traits disponíveis no projeto e como usá-los.

## 🏗️ Traits Disponíveis

```text
app/Traits/
├── HasIsActiveScope.php
├── HasNotifications.php
└── BetterEnum.php
```

## ✅ HasIsActiveScope

O trait `HasIsActiveScope` encapsula operações sobre o campo booleano `is_active`.

### O que oferece

| API | Tipo | Uso |
| --- | --- | --- |
| `isActive()` | query scope (`#[Scope]`) | `Model::query()->isActive()->get()` |
| `activate()` | método de instância | marca `is_active = true` |
| `deactivate()` | método de instância | marca `is_active = false` |
| `toggleActive()` | método de instância | inverte o valor atual |
| `countActive(Builder $query)` | helper | conta registros com `is_active = true` |

No MySQL, o scope `isActive()` tenta usar o índice `idx_is_active` quando disponível.

### Uso em Models

```php
use App\Traits\HasIsActiveScope;

class User extends Authenticatable
{
    use HasIsActiveScope;
}
```

### Exemplos

```php
$activeUsers = User::query()->isActive()->get();

$user->activate();
$user->deactivate();
$user->toggleActive();

// Preferir o atributo booleano para checagens de instância:
if ($user->is_active) {
    // ...
}
```

### Restrições

- O model precisa ter a coluna `is_active` (cast `boolean` recomendado).
- O scope é `protected` com atributo `#[Scope]`; chame via query builder (`->isActive()`), não como método público de instância.
- Não existe mais o trait antigo `HasActiveScope`.

## 🔔 HasNotifications

Helpers para disparar notificações Filament com estilo consistente (`notify`, `notifySuccess`, `notifyError`, etc.).

```php
use App\Traits\HasNotifications;

class SomeLivewireComponent
{
    use HasNotifications;

    public function save(): void
    {
        // ...
        $this->notifySuccess('Salvo com sucesso');
    }
}
```

## 🎯 BetterEnum

Utilitários para enums backed:

```php
trait BetterEnum
{
    public static function names(): array;    // nomes dos cases
    public static function values(): array;   // valores persistidos
    public static function options(): array;  // name => value
    public static function asArray(): array;  // name => value
    public static function random(): self;
}
```

Exemplo:

```php
enum Status: string
{
    use BetterEnum;

    case Active = 'active';
    case Inactive = 'inactive';
}

Status::values();   // ['active', 'inactive']
Status::options();  // ['Active' => 'active', 'Inactive' => 'inactive']
```

## 🔧 Criando um Trait Customizado

1. Coloque em `app/Traits/`.
2. Use nomes descritivos (`Has…`, `Can…`).
3. Documente métodos públicos com PHPDoc.
4. Prefira type hints explícitos e early returns.
5. Use `boot{TraitName}` somente quando houver hooks Eloquent.

## 🔗 Próximos Passos

- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Enums e Convenções](09-enums-e-convencoes.md)
