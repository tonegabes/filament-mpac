# Policies e Autorização

Este documento mostra como a autorização está aplicada hoje no projeto e como estender com novas policies.

## 📚 Policies existentes

Atualmente existem policies para:

- `User` (`UserPolicy`)
- `Role` (`RolePolicy`)
- `Permission` (`PermissionPolicy`)

No estado atual, não existem policies dedicadas para `Document`, `Image` ou `Media`.

## 🔐 Padrão utilizado

As policies usam enums de permissões para cada ação:

```php
// app/Policies/UserPolicy.php
public function viewAny(User $user): bool
{
    return $user->can(UserPermissions::All);
}

public function update(User $user, User $model): bool
{
    return $user->can(UserPermissions::Update);
}
```

## 🔎 Descoberta de policy

O Laravel resolve policies por convenção (`Model` -> `Policy`) automaticamente.

Registro manual em `AuthServiceProvider` só é necessário em casos fora da convenção.

## 🧩 Integração com Filament

Filament consome as policies automaticamente para ações do Resource e páginas (`List`, `Create`, `Edit`, `View`).

Se precisar de um check explícito:

```php
public static function canViewAny(): bool
{
    return auth()->user()?->can('viewAny', User::class) ?? false;
}
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

Exemplo:

```php
it('denies user without permission to create users', function (): void {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});
```

## 🚧 Quando criar novas policies

Crie policy quando um novo módulo precisar de regras de autorização explícitas (ex.: novo Resource com create/edit/delete).

No cenário atual, os Resources de arquivos estão majoritariamente em modo leitura, por isso ainda não há policy dedicada para eles.

## 🎯 Boas Práticas

1. Mantenha regras de autorização nas policies, não em controllers/pages.
2. Use enums para permissão em vez de strings soltas.
3. Evite lógica complexa no Resource se ela pertence ao domínio de acesso.
4. Cubra cada policy com testes de feature.
5. Sempre validar impacto do `Gate::before` em cenários de segurança.

## 🔗 Próximos Passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Testes](13-testes.md)
