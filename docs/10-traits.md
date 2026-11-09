# Traits

Este documento explica os Traits disponíveis no projeto e como usá-los.

## 🏗️ Traits Disponíveis

```text
app/Traits/
├── HasIsActiveScope.php
└── HasNotifications.php
```

## ✅ HasIsActiveScope

O trait `HasIsActiveScope` encapsula operações sobre o campo booleano `is_active`.

### O que oferece

| API | Tipo | Uso |
| --- | --- | --- |
| `active()` | query scope (`#[Scope]`) | `Model::query()->active()->get()` |
| `isActive()` | método de instância | retorna `(bool) $this->is_active` |
| `isInactive()` | método de instância | negação de `isActive()` |
| `activate()` | método de instância | marca `is_active = true` |
| `deactivate()` | método de instância | marca `is_active = false` |
| `toggleActive()` | método de instância | inverte o valor atual |

No MySQL, o scope `active()` tenta usar o índice `idx_is_active` quando disponível.

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

if ($user->isActive()) {
    // ...
}

if ($user->isInactive()) {
    // ...
}

$user->activate();
$user->deactivate();
$user->toggleActive();
```

### Restrições

- O model precisa ter a coluna `is_active` (cast `boolean` recomendado).
- O scope é `protected` com atributo `#[Scope]`; chame via query builder (`->active()`).
- Não use `isActive()` como se fosse scope de query — na instância ele retorna `bool`.
- Não existe mais o trait antigo `HasActiveScope`, nem helpers `countActive()` / `activeCount()`.

Cobertura: `tests/Unit/Traits/HasActiveScopeTest.php`.

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

Utilitários para enums backed vêm do pacote **`tonegabes/mpac-essentials`** (não há mais `App\Traits\BetterEnum`):

```php
use ToneGabes\MpacEssentials\Traits\BetterEnum;

enum Status: string
{
    use BetterEnum;

    case Active = 'active';
    case Inactive = 'inactive';
}

Status::values();   // ['active', 'inactive']
Status::options();  // ['active' => 'Active', 'inactive' => 'Inactive']
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
