# Deploy, Docker e CI

Runbook da imagem de produção (FrankenPHP) e do pipeline GitLab CI. Fonte da verdade: `Dockerfile`, `Caddyfile`, `docker/php/opcache.ini`, `.dockerignore` e `.gitlab-ci.yml`.

## 🎯 Intent

A app sobe em container **PHP 8.4 + FrankenPHP (Alpine)** via imagem base `serversideup/php:8.4-frankenphp-alpine`. O CI:

1. Roda qualidade (analyze, lint, tests) em `main`, `develop` e MRs.
2. Constrói e publica imagem de MR **somente quando o target é `develop`**, com health check.
3. Em tags, publica imagem de release (`:stable` + `:$CI_COMMIT_TAG`).

Migrations **não** rodam no start do container; permanecem passo de deploy.

## 🧱 Arquitetura da imagem

Multi-stage:

| Stage | Base | Função |
| --- | --- | --- |
| `base` | `serversideup/php:8.4-frankenphp-alpine` | Extensões PHP + `poppler-utils` (PDF) |
| `build` | `base` | `composer install --no-dev` + `package:discover` |
| `assets` | `node:24-alpine` | `npm ci` + `npm run build` (`HUSKY=0`) |
| final | `base` | App + `public/build` + Caddyfile + opcache |

### Extensões e pacotes

PHP (via `install-php-extensions`): `intl`, `exif`, `ldap`, `bcmath`, `gd`.

APK: apenas `poppler-utils` (as libs de runtime das extensões já vêm do instalador).

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

Rota registrada em `bootstrap/app.php` (`health: '/up'`). Intervalo 10s, timeout 3s, `start-period` 30s, 3 retries.

### Opcache

`docker/php/opcache.ini`: opcache ligado, `validate_timestamps=0` (código imutável na imagem). Sem JIT explícito neste arquivo.

### Caddy (FrankenPHP)

`Caddyfile` escuta `:8080`, root em `public/`, `php_server` + `file_server`.

- Cache imutável só em `/build/*` (assets Vite).
- Headers: `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection: 0`, `Referrer-Policy`, `Permissions-Policy`.
- `auto_https off` / `admin off` (TLS fica a cargo do ambiente externo, se houver).

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

## ⚠️ Pitfalls

| Sintoma | Causa | Ação |
| --- | --- | --- |
| Imagem de MR não builda | Target do MR ≠ `develop` | Abrir/retarget MR para `develop` |
| Health check timeout no CI | app não responde `/up` a tempo | ver logs do container; `start-period` é 30s + retries |
| Assets 404 em produção | stage `assets` falhou ou `public/build` não copiado | conferir `npm run build` no stage Node 24 |
| Cache antigo de `/livewire/*` | removido do Caddyfile de propósito | só `/build/*` tem cache longo |
| Discovery cache com pacotes `--dev` | caches locais no contexto | build limpa `bootstrap/cache/*`; `.dockerignore` ignora caches |
| Expectativa de migrate no boot | `AUTORUN_LARAVEL_MIGRATION=false` | rode migrate no passo de deploy |
| Build lento / contexto grande | arquivos locais desnecessários | revisar `.dockerignore` (`public/build`, `vendor`, tests, sqlite) |

## 🔗 Relacionados

- [Setup, Dependências e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
- [Estrutura do Projeto](01-estrutura-do-projeto.md)
- Health: `bootstrap/app.php` → `/up`
