# Deploy, Docker e CI

Runbook da imagem de produção (**Laravel Octane + FrankenPHP**) e do pipeline GitLab CI. Fonte da verdade: `Dockerfile`, `Caddyfile`, `config/octane.php`, `docker/php/opcache.ini`, `.dockerignore` e `.gitlab-ci.yml`.

## 🎯 Intent

A app sobe em container **PHP 8.4 + FrankenPHP** (imagem Debian `serversideup/php:8.4-frankenphp`) com o processo web:

```text
php artisan octane:start --server=frankenphp --port=8080 --caddyfile=/etc/frankenphp/Caddyfile
```

Isso mantém a aplicação em memória (worker mode) em vez do modelo clássico request → boot PHP-FPM. O CI:

1. Roda qualidade (analyze, lint, tests) em `main`, `develop` e MRs.
2. Constrói e publica imagem de MR **somente quando o target é `develop`**, com health check.
3. Em tags, publica imagem de release (`:stable` + `:$CI_COMMIT_TAG`).

Migrations **não** rodam no start do container; permanecem passo de deploy.

> **Dev local:** `composer run dev` continua com `php artisan serve` (porta 8000). Octane é o caminho de **produção/container**; local só use Octane se precisar reproduzir o runtime.

## ⚡ Laravel Octane (produção)

| Peça | Valor no projeto |
| --- | --- |
| Pacote | `laravel/octane` `^2.25` |
| Server padrão | `frankenphp` via `OCTANE_SERVER` / `config/octane.php` |
| Config | `config/octane.php` |
| Env exemplo | `OCTANE_SERVER=frankenphp` em `.env.example` |
| Entrypoint Docker | `CMD` do `Dockerfile` (acima) |
| Worker HTTP | `public/frankenphp-worker.php` (gerado; ver abaixo) |
| State file | `storage/logs/octane-server-state.json` (`OCTANE_STATE_FILE`) |

### O que o Caddyfile faz no modo Octane

O `Caddyfile` do repositório é copiado para `/etc/frankenphp/Caddyfile` e passado ao `octane:start`:

- Bloco global `frankenphp { worker { … } }` aponta para `frankenphp-worker.php` em `APP_PUBLIC_PATH` (default `/var/www/html/public`).
- Site `:8080` usa `php_server` com `index` / `try_files` no worker (não `index.php`).
- Admin Caddy em `{$CADDY_SERVER_ADMIN_HOST:localhost}:{$CADDY_SERVER_ADMIN_PORT:2019}` — necessário para `octane:status` / `octane:reload` / `octane:stop`.
- `auto_https off` (TLS externo, se houver).
- Cache imutável só em `/build/*`; headers de segurança iguais ao padrão anterior.

### `frankenphp-worker.php` e artefatos gitignored

`.gitignore` ignora:

```text
**/caddy
frankenphp
frankenphp-worker.php
```

- Binário local `frankenphp` (download do `octane:install`) **não** entra na imagem — a imagem base já traz FrankenPHP.
- `public/frankenphp-worker.php` é criado por `php artisan octane:install --server=frankenphp` ou recriado no start se `public/` for gravável.
- Em builds com filesystem read-only no start, publique o stub no stage de build (ex.: copiar de `vendor/laravel/octane/.../frankenphp-worker.php` para `public/`) ou rode o install no build. O `Dockerfile` atual depende de `public/` gravável no boot (usuário `www-data` + dirs de storage/bootstrap).

### Config relevante (`config/octane.php`)

| Opção | Valor atual | Nota |
| --- | --- | --- |
| `server` | `env('OCTANE_SERVER', 'frankenphp')` | Único server suportado neste deploy |
| `https` | `OCTANE_HTTPS` (default `false`) | Links absolutos HTTPS se `true` |
| `garbage` | `50` | GC forçado após ~50 MB no worker |
| `max_execution_time` | `30` | Limite por request no Octane |
| `watch` | `app`, `bootstrap`, `config/**/*.php`, … | Usado só com `--watch` (dev) |
| `OperationTerminated` | `FlushOnce`, `FlushTemporaryContainerInstances` | `DisconnectFromDatabases` está comentado |
| `OperationReleased` | (vazio) | `CollectGarbage` / `ReleaseFreedMemory` comentados |

### Spatie Permission sob Octane

Em `config/permission.php`:

```php
'register_octane_reset_listener' => false,
```

Permissões **não** são re-flushed automaticamente a cada request Octane. Se roles/perms mudarem em runtime e o worker parecer “desatualizado”, habilite o listener ou rode `octane:reload` após sync de permissões.

### Comandos úteis (dentro do container)

```bash
php artisan octane:status
php artisan octane:reload
php artisan octane:stop
```

Reload após deploy de código/config sem recrear o container (quando o processo Octane já está no ar).

## 🧱 Arquitetura da imagem

Multi-stage:

| Stage | Base | Função |
| --- | --- | --- |
| `base` | `serversideup/php:8.4-frankenphp` | Extensões PHP + `poppler-utils` (PDF) via **apt** |
| `build` | `base` | `composer install --no-dev` + `package:discover` |
| `assets` | `node:24-alpine` | `npm ci` + `npm run build` (`HUSKY=0`) |
| final | `base` | App + `public/build` + Caddyfile + opcache + `CMD` Octane |

A tag **não** é mais `…-frankenphp-alpine`. A base Debian alinha com o runtime FrankenPHP/Octane da serversideup; `poppler-utils` instala com `apt-get` (não `apk`).

### Extensões e pacotes

PHP (via `install-php-extensions`): `intl`, `exif`, `ldap`, `bcmath`, `gd`.

Sistema: `poppler-utils` (PDF de relatórios).

### Autorun (serversideup)

No stage final:

```dockerfile
ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=false \
    AUTORUN_LARAVEL_STORAGE_LINK=false
```

- Caches de config/route/view/event dependem de runtime (`APP_KEY`, `APP_URL`, …) e são gerados no **start** pela automação da imagem — **não** no build.
- `storage:link` é criado no build; `AUTORUN_LARAVEL_STORAGE_LINK=false` evita refazer no start.
- Migrations ficam fora do autorun (`AUTORUN_LARAVEL_MIGRATION=false`).

### Health check

```text
GET http://localhost:8080/up
```

Implementação no `Dockerfile`: `curl -fsS` (a imagem Debian não depende de `wget`). Rota em `bootstrap/app.php` (`health: '/up'`). Intervalo 10s, timeout 3s, `start-period` 30s, 3 retries.

### Opcache

`docker/php/opcache.ini`: opcache ligado, `validate_timestamps=0` (código imutável na imagem). Sem JIT explícito neste arquivo.

### Contexto Docker (`.dockerignore`)

Exclui, entre outros: `vendor`, `node_modules`, `tests`, `public/hot`, `public/build`, `public/storage`, SQLite local, `.composer-cache`, caches de `bootstrap`/`storage`, docs (`*.md`), e arquivos de compose/Dockerfile do contexto.

`public/build` é gerado no stage `assets` e copiado para a imagem final — não precisa estar no contexto.

## 🔄 Pipeline GitLab (`.gitlab-ci.yml`)

Stages: `prepare` → `quality` → `build`.

### Variáveis relevantes

| Variável | Uso |
| --- | --- |
| `APP_IMAGE` | `${MPAC_REGISTRY_URL}/${CI_PROJECT_PATH}` |
| `BUILD_IMAGE` | `docker:27.5.1-cli` |
| `DOCKER_BUILDKIT` | `1` |
| Cache Composer | `.composer-cache/` keyed por `composer.lock` |

Credenciais de registry: `CI_REGISTRY_USER` / `CI_REGISTRY_PASSWORD` + `MPAC_REGISTRY_URL` (login no `before_script` dos jobs de build).

### Jobs de qualidade

Rodam em `main`, `develop` e MRs (`composer_install` → `code_analysis` / `style_check` / `tests`):

| Job | Comando |
| --- | --- |
| `code_analysis` | `composer analyze` |
| `style_check` | `composer lint` |
| `tests` | `composer test` |

Imagem de análise: `registry.mpac.mp.br/sistemas/docker-images/laravel-ci:php84-test` (tag `code_analysis`).

### `build_mr_image`

**Quando:** MR com target `develop` (`CI_MERGE_REQUEST_TARGET_BRANCH_NAME == "develop"`).

**O que faz:**

1. `docker buildx build` com cache inline e `--provenance=false --sbom=false`.
2. Tags: `${APP_IMAGE}:${CI_COMMIT_SHORT_SHA}` e `${APP_IMAGE}:mr-${CI_MERGE_REQUEST_IID}`.
3. Sobe um container com envs mínimos de smoke (`APP_ENV=testing`, SQLite, cache/session/queue em memória) e espera o Docker `Health.Status == healthy` (até ~60s).

MR para outras branches **não** gera imagem.

### `release_image`

**Quando:** pipeline de tag (`$CI_COMMIT_TAG`).

Tags publicadas: `${APP_IMAGE}:${CI_COMMIT_TAG}` e `${APP_IMAGE}:stable`. Usa `resource_group: production-build`.

## 🖥️ Build local (smoke)

```bash
docker build -t filament-mpac:local .
docker run --rm -d --name mpac-local \
  -e APP_ENV=testing \
  -e APP_DEBUG=false \
  -e APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
  -e OCTANE_SERVER=frankenphp \
  -e DB_CONNECTION=sqlite \
  -e CACHE_STORE=array \
  -e SESSION_DRIVER=array \
  -e QUEUE_CONNECTION=sync \
  -p 8080:8080 \
  filament-mpac:local

# Health (mesma ideia do CI)
docker inspect --format '{{.State.Health.Status}}' mpac-local
curl -fsS http://localhost:8080/up
```

Ajuste volumes/env de DB real conforme o ambiente de staging; o exemplo acima espelha o smoke do job de MR.

### Octane local (opcional)

Só necessário para depurar comportamento de worker / state leak:

```bash
composer require laravel/octane   # já no composer.json
php artisan octane:install --server=frankenphp
php artisan octane:start --server=frankenphp --port=8000 --watch
```

Não misture com `composer run dev` (que sobe `artisan serve` na mesma faixa de portas).

## ⚠️ Pitfalls

| Sintoma | Causa | Ação |
| --- | --- | --- |
| Imagem de MR não builda | Target do MR ≠ `develop` | Abrir/retarget MR para `develop` |
| Health check timeout no CI | Octane/Caddy não sobe a tempo ou `/up` falha | logs do container; confirmar `CMD` Octane e `start-period` 30s |
| `frankenphp-worker.php` Permission denied | `public/` read-only no start | copiar stub no build ou tornar `public` gravável para o user do processo |
| Estado “vaza” entre requests | singleton / static / service não resetado | revisar listeners em `config/octane.php`; evitar state global em services |
| Permissões Spatie desatualizadas no worker | `register_octane_reset_listener=false` | `octane:reload` após sync, ou ligar o listener |
| Assets 404 em produção | stage `assets` falhou ou `public/build` não copiado | conferir `npm run build` no stage Node 24 |
| Cache antigo de `/livewire/*` | removido do Caddyfile de propósito | só `/build/*` tem cache longo |
| Expectativa de migrate no boot | `AUTORUN_LARAVEL_MIGRATION=false` | rode migrate no passo de deploy |
| Docs/scripts ainda citam Alpine/`apk`/`wget` | base mudou para Debian | usar `serversideup/php:8.4-frankenphp`, `apt`, `curl` |
| Binário `frankenphp` enorme no contexto local | download do `octane:install` | já está no `.gitignore`; não copie para a imagem |

## 🔗 Relacionados

- [Setup, Dependências e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
- [Estrutura do Projeto](01-estrutura-do-projeto.md)
- Health: `bootstrap/app.php` → `/up`
- Config: `config/octane.php`, `config/permission.php` (`register_octane_reset_listener`)
