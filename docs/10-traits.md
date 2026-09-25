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
| `active()` | query scope (`#[Scope]`) | `Model::query()->active()->get()` |
| `activeCount()` | query scope (`#[Scope]`) | `Model::query()->activeCount()` |
| `isActive()` | método de **instância** | `$model->isActive()` → bool |
| `isInactive()` | método de **instância** | `$model->isInactive()` → bool |
| `activate()` / `deactivate()` / `toggleActive()` | instância | persistem `is_active` |

No MySQL, o scope `active()` tenta usar o índice `idx_is_active` quando disponível.

**Não** documente nem chame `isActive()` como scope de query — isso é método de instância. O scope correto é `active()`.

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
$activeUsers = User::query()->active()->get();
$count = User::query()->activeCount();

$user->activate();
$user->deactivate();
$user->toggleActive();

if ($user->isActive()) {
    // ...
}

if ($user->isInactive()) {
    // ...
}
```

### Restrições

- O model precisa ter a coluna `is_active` (cast `boolean` recomendado).
- Scopes são `protected` com atributo `#[Scope]`; chame via query builder (`->active()`).
- Não existe mais o trait antigo `HasActiveScope` nem `scopeIsActive` / `countActive()`.

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
