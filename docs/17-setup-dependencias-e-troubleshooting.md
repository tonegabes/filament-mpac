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
| `composer analyze` | PHPStan (Larastan + pest-plugin-phpstan) |
| `composer lint` / `lint:dirty` | Pint em modo `--test` (não altera arquivos) |
| `composer format` / `format:dirty` | Pint aplicando correções |
| `composer fresh:db` | `migrate:fresh --seed` |
| `composer dev` | serve + queue + pail + vite |

Pint usa o config de `vendor/tonegabes/mpac-essentials/pint.json`. Prefira esses scripts a invocar o `.php-cs-fixer.php` da raiz (ele ainda referencia o pacote antigo `mp-coding-standards`, removido do `composer.json`).

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
| `laravel/pulse` | `^1.7` |
| `opcodesio/log-viewer` | `^3.19` |
| `tonegabes/mpac-essentials` | `^1.1` |
| `janczakb/filament-flex-fields` | `^2.7` |

Fonte da verdade: `composer.json` / `composer.lock`.

PHPStan (`phpstan.neon`, level 8) inclui:

- `larastan/larastan`
- `calebdw/larastan-livewire`
- `pestphp/pest-plugin-phpstan`
- ignores pontuais em `config/pulse.php` para APIs internas do Pulse

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
- **mp-coding-standards → mpac-essentials**: scripts de lint/format usam Pint do essentials; não reintroduza o pacote antigo sem alinhar `.php-cs-fixer.php`.

## 🔐 Auth local vs LDAP

Variáveis críticas:

```env
AUTH_MODE=local          # ou ldap
LDAP_AUTH_REQUIRES_LOCAL=false
LDAP_AUTH_EMAIL_DOMAIN=@mpdomain.br
```

Codepaths:

- Resolução: `AuthModeHandlerResolver` → `LocalAuthModeHandler` / `LdapAuthModeHandler`
- Página: `App\Filament\Pages\Auth\Login`
- Serviços LDAP: `LdapAuthService`, `LdapUserService`

Pitfalls:

- Com `LDAP_AUTH_REQUIRES_LOCAL=true`, usuários LDAP sem registro local ativo falham no login.
- Username LDAP é normalizado (lowercase, trim, remove domínio se colado no valor).
- Registro local só existe no modo local e quando o panel provider habilita registration.

## 🧭 Painéis e ferramentas

Dois painéis (`App\Enums\Panels`):

- `app` → `/` (permissão `panels.view.app`)
- `admin` → `/admin` (permissão `panels.view.admin`)

Troca no menu do usuário: `PanelSwitcher::userMenuItems()`.

Ferramentas no grupo Tools do admin:

| Ferramenta | Path | Permissão | Gate / auth |
| --- | --- | --- | --- |
| Log Viewer | `/log-viewer` | `system.log-viewer.access` | `LogViewer::auth` |
| Pulse | `/pulse` | `system.pulse.access` | `viewPulse` |

## 📁 Media Library (pitfalls)

- Models usam `Document::fileCollection()` / `Image::fileCollection()` → `FileCollection`.
- Formulários oficiais usam `LibraryFileUpload::mediaLibrary(...)`.
- Não recriar `COLLECTION_NAME` nos models.
- Discos: `images`, `documents`, `public` (logos/fundos).

## ✅ Active scope (pitfalls)

- Query: `Model::query()->active()` e `->activeCount()`.
- Instância: `$model->isActive()` / `isInactive()`.
- Não use `->isActive()` como scope de query (isso chama o método de instância e falha no builder).

## 🐛 Troubleshooting comum

| Sintoma | Causa provável | Ação |
| --- | --- | --- |
| CSS/JS Filament “quebrado” após update | assets públicos desatualizados | `composer dump-autoload` / `php artisan filament:upgrade` e commit dos arquivos em `public/` |
| ViteException / manifesto | frontend não buildado | `npm run build` ou `npm run dev` |
| Permissões “sumiram” | cache Spatie / seed ausente | `PermissionRegistrar::forgetCachedPermissions()` + reseeding |
| Login LDAP falha com usuário novo | `requires_local` ou falta de sync | revisar `auth.ldap.requires_local` e criação em `Login::handleLocalUserRecord()` |
| Usuário autenticado sem acesso ao painel | falta `panels.view.*` | conferir `RoleSeeder` e `User::canAccessPanel()` |
| Testes de role quebrando com `Operator` | rename para `UserRole::User` | atualizar asserts/factories para `User` |
| `composer lint:fix` não existe | script renomeado | use `composer format` / `format:dirty` |
| `/pulse` ou `/log-viewer` 403 | falta permissão | conceder `PulseAccess` / `LogViewerAccess` (Developer tem `*`) |
| PHP-CS-Fixer falha ao exigir `mp-coding-standards` | arquivo legado | use `composer format`; alinhe ou remova `.php-cs-fixer.php` se ainda for necessário |

## 🧪 Verificação mínima após mudanças de auth/deps

```bash
php artisan test --compact tests/Feature/Filament/LoginTest.php
php artisan test --compact tests/Feature/Seeders/RoleSeederTest.php
php artisan test --compact tests/Unit/Services/Auth/
php artisan test --compact tests/Unit/Traits/HasActiveScopeTest.php
php artisan test --compact tests/Unit/Models/ImageTest.php
php artisan test --compact tests/Unit/Models/DocumentTest.php
```

## 🔗 Próximos Passos

- [Páginas Customizadas](05-paginas-customizadas.md) — login/registro
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Traits](10-traits.md) — `HasIsActiveScope`
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md) — media library
- [Testes](13-testes.md)
- [Panel Provider](15-panel-provider.md)
