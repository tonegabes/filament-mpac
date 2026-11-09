# Policies e Autorização

Este documento mostra como a autorização está aplicada hoje no projeto e como estender com novas policies.

## 📚 Policies existentes

| Model / subject | Policy | Registro |
| --- | --- | --- |
| `User` | `UserPolicy` | convenção Laravel |
| `Role` | `RolePolicy` | convenção |
| `Permission` | `PermissionPolicy` | convenção |
| `Document` | `DocumentPolicy` | convenção |
| `Image` | `ImagePolicy` | convenção |
| `Media` (Spatie) | `MediaPolicy` | **manual** em `AuthServiceProvider::$policies` |
| `Activity` (Spatie Activitylog) | `ActivityPolicy` | **manual** em `AuthServiceProvider::$policies` |

Models de pacote (`Media`, `Activity`) não seguem a convenção `App\Models` → precisam do mapa em `AuthServiceProvider`.

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

`ActivityPolicy` é view-only (`viewAny` / `view` via `ActivityPermissions`).

## 🔎 Descoberta de policy

O Laravel resolve policies por convenção (`App\Models\X` → `App\Policies\XPolicy`) automaticamente.

Registro manual em `AuthServiceProvider` só é necessário para subjects fora de `App\Models` (hoje: `Activity` e `Media`).

```php
protected $policies = [
    Activity::class => ActivityPolicy::class,
    Media::class => MediaPolicy::class,
];
```

## 🧩 Integração com Filament

Filament consome as policies automaticamente para ações do Resource e páginas (`List`, `Create`, `Edit`, `View`).

Se precisar de um check explícito:

```php
public static function canViewAny(): bool
{
    return auth()->user()?->can('viewAny', User::class) ?? false;
}
```

## 🛡️ Gates

`AuthServiceProvider::configureGates()` está vazio no código atual — **não** há `Gate::before` de super-role.

O gate `viewPulse` vive em `AppServiceProvider::configurePulse()` e exige `SystemPermissions::PulseAccess`.

## 🧪 Testes de policy

Arquivos de referência em `tests/Feature/Policies/`:

- `UserPolicyTest.php`, `RolePolicyTest.php`, `PermissionPolicyTest.php`
- `DocumentPolicyTest.php`, `ImagePolicyTest.php`, `MediaPolicyTest.php`
- `ActivityPolicyTest.php`

Exemplo:

```php
it('denies user without permission to create users', function (): void {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});
```

## 🚧 Quando criar novas policies

1. Crie a policy (`php artisan make:policy … --model=…`).
2. Use o enum de permissões correspondente.
3. Se o subject for de pacote (não `App\Models`), registre em `AuthServiceProvider::$policies`.
4. Cubra com teste em `tests/Feature/Policies`.

## 🎯 Boas Práticas

1. Mantenha regras de autorização nas policies, não em controllers/pages.
2. Use enums para permissão em vez de strings soltas.
3. Evite lógica complexa no Resource se ela pertence ao domínio de acesso.
4. Cubra cada policy com testes de feature.
5. Não reintroduza bypass global sem revisão de segurança explícita.

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Testes](13-testes.md)
