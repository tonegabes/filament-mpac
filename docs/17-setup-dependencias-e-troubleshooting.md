# Setup, Dependências e Troubleshooting

Runbook operacional para desenvolvedores: ambiente local, upgrades de dependências e falhas comuns.

## 🚀 Setup rápido

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build   # ou: npm run dev / composer run dev
```

Comandos úteis do `composer.json`:

| Script | Função |
| --- | --- |
| `composer test` | limpa config e roda a suite Pest |
| `composer analyze` | PHPStan |
| `composer lint` / `lint:fix` | Pint via coding standards do MP |
| `composer fresh:db` | `migrate:fresh --seed` |
| `composer dev` | serve + queue + pail + vite |

## 📦 Versões atuais (composer)

Stack relevante após o bump de dependências:

| Pacote | Constraint |
| --- | --- |
| PHP | `^8.3` (ambiente típico 8.4) |
| Laravel | `^13` |
| Filament | `~5.0` |
| Livewire | v4 (via Filament) |
| Pest | `^5.0` |
| PHPUnit | `^13.0` |
| `directorytree/ldaprecord-laravel` | `^4.0.4` |
| `spatie/laravel-permission` | `^8.3` |
| `janczakb/filament-flex-fields` | `^2.7` |

Fonte da verdade: `composer.json` / `composer.lock`.

## 🔄 Workflow de update de dependências

1. Atualize constraints (ou use `composer update:requirements` com cuidado).
2. Rode `composer update` e revise o diff de `composer.lock`.
3. O script `post-autoload-dump` já executa `php artisan filament:upgrade` — isso republica CSS/JS em `public/`.
4. Confirme assets Filament/flex-fields versionados em `public/css` e `public/js`.
5. Rode a suíte mínima afetada, depois `composer test` se o bump for amplo.
6. Commit separado (`chore: update dependencies`) sem misturar features.

### O que o bump recente mudou

- **Pest 4 → 5** e **PHPUnit 12 → 13**: revise plugins e asserts se algo quebrar na suite.
- **ldaprecord-laravel 3 → 4**: valide fluxo LDAP (`AUTH_MODE=ldap`) em staging.
- **spatie/laravel-permission 6 → 8**: seeders/roles atuais usam a API estável do projeto; evite APIs removidas de teams/guards customizados sem checar o changelog do pacote.

## 🔐 Auth local vs LDAP

Variáveis críticas:

```env
AUTH_MODE=local          # ou ldap
LDAP_AUTH_REQUIRES_LOCAL=false
LDAP_AUTH_EMAIL_DOMAIN=@mpdomain.br
```

`LDAP_AUTH_EMAIL_DOMAIN` alimenta `config('auth.ldap.email_domain')` (default em `config/auth.php`: `@mpac.mp.br` se o env não estiver setado).

Codepaths:

- Resolução: `AuthModeHandlerResolver` → `LocalAuthModeHandler` / `LdapAuthModeHandler`
- Página: `App\Filament\Pages\Auth\Login` (suffix no username; `normalizeUsername()` remove o domínio se colado)
- Serviços LDAP: `LdapAuthService` (login = username + domínio), `LdapUserService`
- Admin CRUD: `App\Filament\Resources\Users\Schemas\UserForm` — em LDAP, e-mail readonly gerado a partir do username

Pitfalls:

- Com `LDAP_AUTH_REQUIRES_LOCAL=true`, usuários LDAP sem registro local ativo falham no login.
- Username LDAP é normalizado (lowercase, trim, remove domínio se colado no valor).
- Registro local só existe no modo local e quando o panel provider habilita registration.
- No form de usuário (LDAP), digitar o domínio no username gera e-mail inválido (`user@domain@domain`) — digite só o sAMAccountName/uid.
- Testes de `UserResource` em CI geralmente rodam com `AUTH_MODE=local`; para cobrir o ramo LDAP do form, setar `Config::set('auth.mode', 'ldap')` (+ `auth.ldap.email_domain`) no teste.

## 🧭 Painéis

Dois painéis (`App\Enums\Panels`):

- `app` → `/` (permissão `panels.view.app`)
- `admin` → `/admin` (permissão `panels.view.admin`)

Troca no menu do usuário: `PanelSwitcher::userMenuItems()`.

## 🐛 Troubleshooting comum

| Sintoma | Causa provável | Ação |
| --- | --- | --- |
| CSS/JS Filament “quebrado” após update | assets públicos desatualizados | `composer dump-autoload` / `php artisan filament:upgrade` e commit dos arquivos em `public/` |
| ViteException / manifesto | frontend não buildado | `npm run build` ou `npm run dev` |
| Permissões “sumiram” | cache Spatie / seed ausente | `PermissionRegistrar::forgetCachedPermissions()` + reseeding |
| Login LDAP falha com usuário novo | `requires_local` ou falta de sync | revisar `auth.ldap.requires_local` e criação em `Login::handleLocalUserRecord()` |
| E-mail do UserForm LDAP errado / com domínio duplicado | username inclui suffix ou `LDAP_AUTH_EMAIL_DOMAIN` inconsistente | username sem domínio; alinhar env com login e `UserForm` |
| E-mail do UserForm editável em ambiente LDAP | `AUTH_MODE` não é `ldap` no processo PHP | checar `.env` / `config:clear`; schema lê `config('auth.mode')` no render |
| Usuário autenticado sem acesso ao painel | falta `panels.view.*` | conferir `RoleSeeder` e `User::canAccessPanel()` |
| Testes de role quebrando com `Operator` | rename para `UserRole::User` | atualizar asserts/factories para `User` |

## 🧪 Verificação mínima após mudanças de auth/deps

```bash
php artisan test --compact tests/Feature/Filament/LoginTest.php
php artisan test --compact tests/Feature/Filament/UserResourceTest.php
php artisan test --compact tests/Feature/Seeders/RoleSeederTest.php
php artisan test --compact tests/Unit/Services/Auth/
```

## 🔗 Próximos Passos

- [Páginas Customizadas](05-paginas-customizadas.md) — login/registro
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Testes](13-testes.md)
- [Panel Provider](15-panel-provider.md)
