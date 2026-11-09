# Filament MPAC

Template Laravel + Filament para novos sistemas da organização. Stack: **PHP 8.4**, **Laravel 13**, **Filament 5**, **Livewire 4**, **Pest 5**, **Tailwind 4**.

## Documentação

O guia completo está em **[docs/README.md](docs/README.md)** (arquitetura, recursos Filament, permissões, testes, Docker e CI).

## Setup rápido

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Ambiente com tudo junto (serve, fila, Pail e Vite):

```bash
composer run dev
```

## Acesso local

| Painel | URL (com `php artisan serve`) |
| --- | --- |
| App | http://127.0.0.1:8000/ |
| Admin | http://127.0.0.1:8000/admin |
| Login (redirect) | http://127.0.0.1:8000/entrar |
| Pulse | http://127.0.0.1:8000/pulse |

Após `php artisan db:seed`:

| Papel | E-mail | Senha |
| --- | --- | --- |
| Developer | `developer@email.com` | `developer` |
| Admin | `admin@email.com` | `admin` |
| User | `user@email.com` | `user` |

## Qualidade de código

| Comando | Uso |
| --- | --- |
| `composer format` / `format:dirty` | Laravel Pint (config: `pint.json` na raiz) |
| `composer lint` / `lint:dirty` | Pint em modo `--test` |
| `composer analyze` | PHPStan |
| `composer test` | Pest |

Antes de commitar, o Husky roda `lint-staged` (format nos PHP alterados).

## Scaffold de domínio

```bash
php artisan make:model-plus NomeDoModel --resource=nome-recurso --migration --factory --seed
```

Pacote: `tonegabes/laravel-make-model-plus`.

## Docker / CI

Imagem FrankenPHP e pipeline GitLab: [docs/18-deploy-docker-e-ci.md](docs/18-deploy-docker-e-ci.md). Variáveis `APP_IMAGE` e `APP_VERSION` no `.env` são usadas pelo `docker-compose.yml` de build — ajuste ao publicar a imagem do projeto derivado.

## Licença

MIT
