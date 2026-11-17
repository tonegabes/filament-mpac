# Traits

Este documento explica os Traits disponíveis no projeto e como usá-los.

## 🏗️ Traits Disponíveis

```text
app/Traits/
├── HasIsActiveScope.php
├── HasNotifications.php
├── HasBlameable.php
└── ResolvesResourcePermissions.php
```

## ✅ HasIsActiveScope

O trait `HasIsActiveScope` encapsula o campo booleano `is_active`.

### O que oferece

| API | Tipo | Uso |
| --- | --- | --- |
| `active()` | query scope (`#[Scope]`) | `Model::query()->active()->get()` |
| `isActive()` | método de **instância** | `(bool) $model->is_active` |
| `isInactive()` | método de instância | inverso de `isActive()` |
| `activate()` | método de instância | `is_active = true` |
| `deactivate()` | método de instância | `is_active = false` |
| `toggleActive()` | método de instância | inverte o valor |

Não existem helpers `activeCount()` / `countActive()`, nem hint de índice MySQL `useIndex('idx_is_active')`.

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

$user->activate();
$user->deactivate();
$user->toggleActive();

if ($user->isActive()) {
    // ...
}
```

### Restrições

- O model precisa ter a coluna `is_active` (cast `boolean` recomendado).
- O scope de query é `active()` (protegido + `#[Scope]`); **não** chame `isActive()` no builder — esse nome é só de instância.
- Não existe mais o trait antigo `HasActiveScope`.

## 🧾 HasBlameable

Preenche automaticamente `created_by` / `updated_by` com o usuário autenticado e expõe relações:

```php
use App\Traits\HasBlameable;

class User extends Authenticatable
{
    use HasBlameable;
}

$model->creator; // BelongsTo User via created_by
$model->updater; // BelongsTo User via updated_by
```

Comportamento:

- Em `creating`: preenche `created_by` e `updated_by` se ainda forem `null` e houver `Auth::id()`.
- Em `updating`: sempre atualiza `updated_by` quando houver usuário autenticado.
- Sem usuário autenticado (console, jobs sem actor), os campos não são forçados.

Requer colunas `created_by` / `updated_by` na tabela (presentes em `users`).

## 🧭 ResolvesResourcePermissions

Trait para enums backed que implementam `App\Contracts\ResourcePermissions`:

```php
enum DocumentPermissions: string implements ResourcePermissions
{
    use ResolvesResourcePermissions;

    case ViewAny = 'documents.view.any';
    case View = 'documents.view';
    // ...
}

DocumentPermissions::forAction('viewAny'); // DocumentPermissions::ViewAny
DocumentPermissions::forAction('deleteAny'); // DocumentPermissions::Delete
DocumentPermissions::forAction('unknown'); // null
```

Mapeamento interno: `viewAny`→`ViewAny`, `delete`/`deleteAny`→`Delete`, `restore`/`restoreAny`→`Restore`, `forceDelete`/`forceDeleteAny`→`ForceDelete`, etc. Usado por `ResourcePolicy`.

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

Utilitários para enums backed vêm do pacote **`tonegabes/mpac-essentials`**:

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

Não use mais um `BetterEnum` local em `app/Traits` — foi removido.

## 🔧 Criando um Trait Customizado

1. Coloque em `app/Traits/`.
2. Use nomes descritivos (`Has…`, `Can…`).
3. Documente métodos públicos com PHPDoc.
4. Prefira type hints explícitos e early returns.
5. Use `boot{TraitName}` somente quando houver hooks Eloquent.

## 🔗 Próximos Passos

- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Enums e Convenções](09-enums-e-convencoes.md)
