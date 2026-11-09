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
| `composer analyze` | PHPStan (`pest-plugin-phpstan` + Larastan) |
| `composer lint` / `lint:dirty` | Pint em modo `--test` (não altera arquivos) |
| `composer format` / `format:dirty` | Pint aplicando correções |
| `composer fresh:db` | `migrate:fresh --seed` |
| `composer dev` | serve + queue + pail + vite |

A config do Pint é o `pint.json` na raiz do projeto (espelha o preset MPAC de `tonegabes/mpac-essentials`). `vendor/bin/pint` sem `--config` já usa esse arquivo.

## 🧱 AppServiceProvider (comportamento global)

`App\Providers\AppServiceProvider` concentra defaults de runtime:

| Método / trecho | Quando | Efeito |
| --- | --- | --- |
| `Model::shouldBeStrict(! app()->isProduction())` | sempre | strict mode Eloquent **fora** de produção (lazy loading / attributes inválidos falham cedo) |
| `configureProductionUrl()` | só production | força HTTPS (`URL::forceScheme` + header `X-Forwarded-Proto`) |
| `configureDbCommands()` | só production | `DB::prohibitDestructiveCommands()` |
| `configurePulse()` | sempre | gate `viewPulse` → `SystemPermissions::PulseAccess` |
| `configureComponents()` | sempre | `CreateAction` global como `iconButton()` — ver [Actions](12-actions-customizadas.md) |

## 📦 Versões atuais (composer)

Stack relevante após o bump de dependências:

| Pacote | Constraint |
| --- | --- |
| PHP | `^8.4` |
| Laravel | `^13` |
| Filament | `~5.0` |
| Livewire | v4 (via Filament) |
| Pest | `^5.0` |
| PHPUnit | `^13.0` |
| `larastan/larastan` | `3.14` (versão **exata**, sem `^`) |
| `directorytree/ldaprecord-laravel` | `^4.0.4` |
| `spatie/laravel-permission` | `^8.3` |
| `janczakb/filament-flex-fields` | `^2.7` |

Fonte da verdade: `composer.json` / `composer.lock`.

`composer analyze` usa Larastan via o script do projeto. Ao atualizar análise estática, preserve o pin `3.14` até alguém validar um bump intencional (diff de regras PHPStan pode gerar ruído no CI).

## 🗄️ MySQL / MariaDB e SSL CA

`config/database.php` configura o certificado CA assim (conexões `mysql` e `mariadb`):

```php
use Pdo\Mysql;

'options' => extension_loaded('pdo_mysql') ? array_filter([
    Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
]) : [],
```

Intent: evitar a constante legada `PDO::MYSQL_ATTR_SSL_CA`, deprecada em PHP 8.5. O projeto usa `Pdo\Mysql::ATTR_SSL_CA`.

Uso operacional:

```env
DB_CONNECTION=mysql
MYSQL_ATTR_SSL_CA=/caminho/para/ca.pem
```

Se `MYSQL_ATTR_SSL_CA` estiver vazio, `array_filter` remove a opção e a conexão segue sem SSL CA explícito. Sem a extensão `pdo_mysql`, `options` fica `[]`.

Teste de regressão: `tests/Unit/DatabaseConfigTest.php` (garante a constante nova e ausência de deprecation ao carregar o config).

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
- **larastan `3.14` (pin exato)**: não rode `composer update larastan/larastan` sem alinhar a constraint; o pin evita drift silencioso no CI de análise.
- **MySQL SSL CA via `Pdo\Mysql`**: após upgrades de PHP, não reintroduza `PDO::MYSQL_ATTR_SSL_CA` em `config/database.php`.

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
| Estilo diferente entre `pint` na CLI e o CI | Pint sem config usa preset Laravel | use sempre `pint.json` na raiz ou `composer format`; evite `pint` sem config em outro diretório |
| PHP-CS-Fixer opcional | `.php-cs-fixer.php` delega para `mpac-essentials` | prefira Pint (`composer format`); CS Fixer só se a ferramenta exigir |
| Permissões “sumiram” | cache Spatie / seed ausente | `PermissionRegistrar::forgetCachedPermissions()` + reseeding |
| Login LDAP falha com usuário novo | `requires_local` ou falta de sync | revisar `auth.ldap.requires_local` e criação em `Login::handleLocalUserRecord()` |
| Usuário autenticado sem acesso ao painel | falta `panels.view.*` | conferir `RoleSeeder` e `User::canAccessPanel()` |
| Testes de role quebrando com `Operator` | rename para `UserRole::User` | atualizar asserts/factories para `User` |
| Pulse abre mas retorna 403 | falta gate/permissão | `SystemPermissions::PulseAccess` + gate `viewPulse` em `AppServiceProvider` |
| Botão “Criar” só mostra ícone `+` / label some | `CreateAction::configureUsing(...->iconButton())` em `AppServiceProvider` | esperado; use `->button()` na instância ou ajuste `configureComponents()` — ver [Actions](12-actions-customizadas.md) |
| CreateAction sem ícone Phosphor esperado | `OverrideActionsProvider` não registrado | confirme `bootstrap/providers.php` e [Panel Provider](15-panel-provider.md) |
| Lazy loading / attribute exception em local | `Model::shouldBeStrict(true)` fora de produção | corrija a query/atributo; em produção o strict mode está desligado |
| Deprecation `MYSQL_ATTR_SSL_CA` ao boot/config | uso de `PDO::MYSQL_ATTR_SSL_CA` | use `Pdo\Mysql::ATTR_SSL_CA` (já no `config/database.php`); rode `tests/Unit/DatabaseConfigTest.php` |
| `composer analyze` diverge do CI | Larastan saiu do pin `3.14` | confira constraint exata em `composer.json` e `composer.lock` |
| Logo/fundo antigo continua no disco após troca | limpeza de settings não rodou | `ManageSystem::afterSave()` chama `SystemSettings::cleanLogoDirectory` / `cleanBackgroundsDirectory` — ver [Settings](11-settings.md) |

## 🧪 Verificação mínima após mudanças de auth/deps

```bash
php artisan test --compact tests/Feature/Filament/LoginTest.php
php artisan test --compact tests/Feature/Seeders/RoleSeederTest.php
php artisan test --compact tests/Unit/Services/Auth/
php artisan test --compact tests/Unit/DatabaseConfigTest.php
```

## 🚢 Deploy / Docker

Ambiente de produção e CI estão documentados em [Deploy, Docker e CI](18-deploy-docker-e-ci.md) (`Dockerfile`, `Caddyfile`, `.gitlab-ci.yml`).

Resumo rápido:

- Imagem: FrankenPHP 8.4 Alpine (`serversideup/php`)
- Health: `GET /up` (porta 8080)
- MR image build só com target `develop`
- Migrations não rodam no boot do container

## 🔗 Próximos Passos

- [Deploy, Docker e CI](18-deploy-docker-e-ci.md)
- [Páginas Customizadas](05-paginas-customizadas.md) — login/registro
- [Sistema de Permissões](07-sistema-permissoes.md)
- [Testes](13-testes.md)
- [Panel Provider](15-panel-provider.md)
