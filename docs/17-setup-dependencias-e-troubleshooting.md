# Setup, Dependências e Troubleshooting

Runbook operacional para desenvolvimento local e CI. Verificado contra `composer.json`, `phpstan.neon` e os scripts Composer atuais.

## 🚀 Setup rápido

```bash
composer install
cp .env.example .env   # se ainda não existir
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build          # ou npm run dev
```

Ambiente típico: PHP 8.4 (composer exige `^8.3`), Laravel 13, Filament 5.

## 🧰 Scripts Composer relevantes

| Script | Função |
| --- | --- |
| `composer lint` | Pint em modo teste (`--test`) |
| `composer format` | Pint corrige o estilo |
| `composer lint:dirty` | Lint só em arquivos dirty |
| `composer format:dirty` | Format só em arquivos dirty |
| `composer analyze` | PHPStan level 8 |
| `composer test` | Limpa config e roda Pest |
| `composer fresh:db` | `migrate:fresh --seed` |
| `composer dev` | serve + queue + pail + vite |

Config do Pint vem de `vendor/tonegabes/mpac-essentials/pint.json` (pacote `tonegabes/mpac-essentials`).

Renomeações recentes (não use os nomes antigos):

- `lint:fix` → `format`
- `lint:fix:dirty` → `format:dirty`
- `tonegabes/mp-coding-standards` → `tonegabes/mpac-essentials`

## 🔬 PHPStan

`phpstan.neon` inclui:

- `bleedingEdge.neon`
- `calebdw/larastan-livewire`
- `larastan/larastan`
- `nesbot/carbon`
- `pestphp/pest-plugin-phpstan` (substitui `mrpunyapal/peststan`)
- `phpstan/phpstan-phpunit`

Nível: **8**. Paths cobrem `app`, `tests`, `config`, etc.

```bash
composer analyze
```

## 📦 Dependências que mudaram recentemente

| Área | Antes | Agora |
| --- | --- | --- |
| Coding standards / Pint config | `mp-coding-standards` | `mpac-essentials` (^1.1) |
| Pest PHPStan | `mrpunyapal/peststan` | `pestphp/pest-plugin-phpstan` |
| Testes | Pest 4 / PHPUnit 12 | Pest 5 / PHPUnit 13 |
| Permissões | Spatie Permission 7.x | `^8.3` |
| LDAP | ldaprecord-laravel 3.x | `^4.0.4` |

## 📁 Media Library — contrato atual

Models `Document` e `Image`:

1. Exponham `public static function fileCollection(): FileCollection`
2. Registrem a coleção Spatie a partir desse enum (value, MIME, disk)
3. Forms usam `LibraryFileUpload::mediaLibrary(...)`
4. Testes usam `Storage::fake(Model::fileCollection()->value)` e `toMediaCollection(...)`

Não use mais `COLLECTION_NAME` nos models.

Detalhes: [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md).

## ✅ HasIsActiveScope — contrato atual

- Trait: `App\Traits\HasIsActiveScope`
- Query: `Model::query()->active()` / `->activeCount()`
- Instância: `isActive()`, `isInactive()`, `activate()`, `deactivate()`, `toggleActive()`

Nomes antigos (`HasActiveScope`, `scopeIsActive`, `::isActive()` como scope) estão obsoletos.

Detalhes: [Traits](10-traits.md).

## 🧯 Troubleshooting

### Uploads não abrem no browser

1. Confirme o disco da coleção (`images` / `documents` / `public`).
2. Rode `php artisan storage:link`.
3. Verifique `visibility` pública quando o arquivo precisa de URL pública.

### Pint / lint falha apontando config inexistente

Os scripts Composer usam `vendor/tonegabes/mpac-essentials/pint.json`. Rode `composer install` e confirme o pacote em `composer.json`.

### `.php-cs-fixer.php` ainda referencia `mp-coding-standards`

O arquivo raiz `.php-cs-fixer.php` pode continuar apontando para o pacote antigo removido do `composer.json`. Preferência atual do projeto: **Pint via `composer format` / `composer lint`**. Ajuste o CS Fixer apenas se ainda for usado no fluxo local.

### PHPStan reclama de testes Pest

Garanta `pestphp/pest-plugin-phpstan` instalado e presente em `phpstan.neon`. O include antigo `peststan` foi removido.

### Scope `active()` “não existe”

Use `User::query()->active()`, não `User::isActive()`. `isActive()` é método de instância.

### Testes de media falham com disco inválido

Faça fake do valor da coleção:

```php
Storage::fake(Image::fileCollection()->value);
Storage::fake(Document::fileCollection()->value);
```

### lint-staged

`.lintstagedrc` roda `composer format` e `composer analyze` em `**/*.php`. Se o pre-commit travar, rode esses comandos isolados para ver o erro real.

## 🔗 Próximos passos

- [Estrutura do Projeto](01-estrutura-do-projeto.md)
- [Testes](13-testes.md)
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Traits](10-traits.md)
