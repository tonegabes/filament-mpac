# Policies e Autorização

Este documento mostra como a autorização está aplicada hoje no projeto e como estender com novas policies.

## 📚 Policies existentes

| Model | Policy | Registro |
| --- | --- | --- |
| `User` | `UserPolicy` | convenção |
| `Role` | `RolePolicy` | convenção |
| `Permission` | `PermissionPolicy` | convenção |
| `Document` | `DocumentPolicy` | convenção |
| `Image` | `ImagePolicy` | convenção |
| `Spatie\…\Media` | `MediaPolicy` | **mapa** em `AuthServiceProvider` |
| `Spatie\Activitylog\Models\Activity` | `ActivityPolicy` | **mapa** em `AuthServiceProvider` |

`Activity` e `Media` precisam do mapa explícito porque o model não segue a convenção `App\Models\{Name}` → `App\Policies\{Name}Policy`.

## 🧱 ResourcePolicy (padrão atual)

Quase todas as policies estendem `App\Policies\ResourcePolicy`:

```php
abstract class ResourcePolicy
{
    /** @var class-string<ResourcePermissions&BackedEnum> */
    protected static string $permissions;

    protected function allows(User $user, string $action): bool
    {
        $permission = static::$permissions::forAction($action);

        if ($permission === null) {
            return false;
        }

        return $user->can($permission);
    }

    // viewAny, view, create, update, delete, restore, forceDelete,
    // deleteAny, restoreAny, forceDeleteAny, replicate, reorder
}
```

`forAction()` vem do contrato `ResourcePermissions` + trait `ResolvesResourcePermissions` no enum.

### Policy mínima de módulo

```php
class DocumentPolicy extends ResourcePolicy
{
    protected static string $permissions = DocumentPermissions::class;
}
```

### Exceções em UserPolicy

`UserPolicy` herda o padrão e endurece `update` / `delete` / `forceDelete`:

- exige `canManageUser($target)`
- impede autoexclusão
- impede excluir o último `Developer` (`isLastDeveloper()`)

## 🔎 Descoberta de policy

O Laravel resolve policies por convenção (`Model` → `Policy`) automaticamente.

Registro manual em `AuthServiceProvider::$policies` só para `Activity` e `Media`.

## 🛡️ Gates

`AuthServiceProvider::configureGates()` está vazio — **não** há `Gate::before` / role `TheOneAboveAll`.

Gate de produto existente:

- `viewPulse` em `AppServiceProvider` → `SystemPermissions::PulseAccess`

## 📥 Downloads autorizados

Rotas autenticadas em `routes/web.php` usam `FileDownloadController`:

| Rota | Policy |
| --- | --- |
| `documents.download` | `Gate::authorize('view', $document)` |
| `images.download` | `Gate::authorize('view', $image)` |
| `media.download` | `Gate::authorize('view', $media)` |

Discos `images` e `documents` são **privados** (`storage/app/private/...`); o download streama via controller, não via URL pública de storage.

## 🧩 Integração com Filament

Filament consome as policies automaticamente para ações do Resource e páginas (`List`, `Create`, `Edit`, `View`).

Check explícito quando necessário:

```php
public static function canViewAny(): bool
{
    return auth()->user()?->can('viewAny', User::class) ?? false;
}
```

## 🧪 Testes de policy

Arquivos de referência:

- `tests/Feature/Policies/UserPolicyTest.php`
- `tests/Feature/Policies/RolePolicyTest.php`
- `tests/Feature/Policies/PermissionPolicyTest.php`

Exemplo:

```php
it('denies user without permission to create users', function (): void {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});
```

## 🚧 Quando criar novas policies

1. Crie o enum em `app/Enums/Permissions` implementando `ResourcePermissions`.
2. Rode `php artisan mpac:permissions:sync` e atualize `RoleSeeder` se Admin/User precisarem das permissões.
3. Crie `XPolicy extends ResourcePolicy` com `protected static string $permissions = XPermissions::class`.
4. Se o model for de vendor (fora de `App\Models`), registre em `AuthServiceProvider::$policies`.
5. Cubra com testes de feature.

## 🎯 Boas Práticas

1. Mantenha regras de autorização nas policies, não em controllers/pages.
2. Prefira `ResourcePolicy` + enum em vez de copiar métodos CRUD.
3. Lógica de hierarquia de usuários fica no model (`canManageUser`, etc.) e a policy só orquestra.
4. Cubra cada policy com testes de feature.
5. Não reintroduza bypass global via `Gate::before` sem decisão explícita de segurança.

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Testes](13-testes.md)
