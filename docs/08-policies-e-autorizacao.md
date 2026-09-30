# Policies e Autorização

Este documento mostra como a autorização está aplicada hoje no projeto e como estender com novas policies.

## 📚 Policies existentes

| Model | Policy | Notas |
| --- | --- | --- |
| `User` | `UserPolicy` | enum `UserPermissions` |
| `Role` | `RolePolicy` | enum `RolePermissions` |
| `Permission` | `PermissionPolicy` | enum `PermissionPermissions` |
| `Document` | `DocumentPolicy` | enum `DocumentPermissions` |
| `Image` | `ImagePolicy` | enum `ImagePermissions` |
| `Spatie\Activitylog\Models\Activity` | `ActivityPolicy` | somente `viewAny` / `view`; mutações `false` |
| `Spatie\MediaLibrary\…\Media` | `MediaPolicy` | model vendor; ver implementação atual |

## 🔐 Padrão utilizado

As policies usam enums de permissões para cada ação:

```php
// app/Policies/UserPolicy.php
public function viewAny(User $user): bool
{
    return $user->can(UserPermissions::ViewAny);
}

public function update(User $user, User $model): bool
{
    return $user->can(UserPermissions::Update);
}
```

`ActivityPolicy` é intencionalmente read-only:

```php
public function create(User $user): bool
{
    return false;
}
```

## 🔎 Descoberta de policy

O Laravel resolve policies por convenção (`App\Models\X` → `App\Policies\XPolicy`) automaticamente.

Models **vendor** precisam de registro explícito em `AuthServiceProvider`:

```php
protected $policies = [
    Activity::class => ActivityPolicy::class,
    Media::class => MediaPolicy::class,
];
```

Sem esse mapa, o Filament não aplica a policy correta ao Resource.

## 🧩 Integração com Filament

Filament consome as policies automaticamente para ações do Resource e páginas (`List`, `Create`, `Edit`, `View`).

Se precisar de um check explícito:

```php
public static function canViewAny(): bool
{
    return auth()->user()?->can('viewAny', User::class) ?? false;
}
```

Para Activity (vendor):

```php
$user->can('viewAny', Activity::class);
```

## 🛡️ Gate global

Existe um `Gate::before` para role privilegiada:

```php
Gate::before(fn (User $user) => $user->hasRole('TheOneAboveAll') ? true : null);
```

Esse bypass acontece antes das policies.

## 🧪 Testes de policy

Arquivos de referência:

- `tests/Feature/Policies/UserPolicyTest.php`
- `tests/Feature/Policies/RolePolicyTest.php`
- `tests/Feature/Policies/PermissionPolicyTest.php`
- `tests/Feature/Policies/ActivityPolicyTest.php`

Exemplo:

```php
it('denies user without permission to create users', function (): void {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});
```

## 🚧 Quando criar novas policies

Crie policy quando um novo módulo precisar de regras de autorização explícitas (ex.: novo Resource com create/edit/delete).

Para models de pacotes (Spatie Activitylog, Media Library), registre a policy em `$policies` do `AuthServiceProvider`.

## 🎯 Boas Práticas

1. Mantenha regras de autorização nas policies, não em controllers/pages.
2. Use enums para permissão em vez de strings soltas.
3. Evite lógica complexa no Resource se ela pertence ao domínio de acesso.
4. Cubra cada policy com testes de feature.
5. Sempre validar impacto do `Gate::before` em cenários de segurança.
6. Audit trail: não libere create/update/delete em `ActivityPolicy`.

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Logs de Atividade](18-logs-de-atividade.md)
- [Testes](13-testes.md)
