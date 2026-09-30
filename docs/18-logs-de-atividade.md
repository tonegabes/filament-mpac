# Logs de Atividade

Runbook do audit trail com `spatie/laravel-activitylog` (^5): o que é registrado, quem pode ver e como a UI Filament consome os logs.

## 📚 Visão geral

| Camada | Onde |
| --- | --- |
| Pacote | `spatie/laravel-activitylog` — model vendor `Spatie\Activitylog\Models\Activity` |
| Helper de UI | `App\Support\ActivityLog` (labels, cores, diffs, opções de filtro) |
| Resource | `App\Filament\Resources\Activities\ActivityResource` (list + view) |
| Action | `App\Filament\Actions\ViewActivitiesAction` (slide-over “Histórico”) |
| Relation manager | `App\Filament\RelationManagers\ActivitiesRelationManager` |
| Permissões | `App\Enums\Permissions\ActivityPermissions` (somente leitura) |
| Policy | `App\Policies\ActivityPolicy` (create/update/delete sempre `false`) |

Grupo de navegação: `NavGroups::Tools` (“Logs de atividade”).

## 🔐 Permissões

```text
activities.*
activities.view.any
activities.view
```

- `ActivityPolicy::viewAny` / `view` usam `ViewAny` e `View`.
- Mutações (`create`, `update`, `delete`, `restore`, `forceDelete`) retornam `false` — logs são imutáveis pela UI.
- Registro da policy é **manual** em `AuthServiceProvider` (model vendor sem convenção `App\Models`):

```php
protected $policies = [
    Activity::class => ActivityPolicy::class,
    Media::class => MediaPolicy::class,
];
```

### Seed

`RoleSeeder`:

| Role | Activity |
| --- | --- |
| `Developer` | `*` (wildcard) |
| `Admin` | `ActivityPermissions::All` |
| `User` | sem permissão de activities |

## 🧱 Quais models logam

| Model | Trait | O que registra |
| --- | --- | --- |
| `User` | `HasActivity` | `name`, `username`, `email`, `is_active` + dirty only |
| `Document` | `LogsActivity` | `name` + dirty only |
| `Image` | `LogsActivity` | `$this->fillable` + dirty only |
| `Role` | `LogsActivity` | `name`, `guard_name` + dirty only |
| `Permission` | `LogsActivity` | `name`, `guard_name` + dirty only |

`HasActivity` (User) combina logging + relações de causer/subject. Nos demais, use `LogsActivity`.

Relação correta no Spatie v5 para “atividades deste registro”:

```php
$record->activitiesAsSubject(); // HasMany Activity
```

Não use `activities()` como se fosse só subject — no User com `HasActivity` o significado das relações é mais amplo.

### Exemplo de options

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'username', 'email', 'is_active'])
        ->logOnlyDirty()
        ->dontLogEmptyChanges();
}
```

## 🖥️ UI Filament

### ActivityResource

- Rotas: `index` (lista) e `view` (infolist com diffs).
- Query eager-load: `causer` e `subject`.
- Tabela/filtros usam `ActivityLog::eventOptions()` e `subjectTypeOptions()`.
- Registrado explicitamente em `AdminPanelProvider` (lista `->resources([...])`), não via `discoverResources`.

### ViewActivitiesAction

Action de tabela (`name` default: `activities`):

- Visível só se o user `can('viewAny', Activity::class)` **e** o record tem `activitiesAsSubject()`.
- Slide-over read-only com até **30** eventos recentes.
- Usada em: Users, Documents, Images, Roles, Permissions.

```php
->recordActions([
    ViewActivitiesAction::make(),
])
```

### ActivitiesRelationManager

- Relationship: `activitiesAsSubject`.
- `isReadOnly(): true`.
- Reutiliza `ActivitiesTable::configure($table, forSubject: true)`.
- Anexado aos Resources de User, Document, Image, Role e Permission.

## 🧰 App\Support\ActivityLog

Helper estático para a UI (não substitui o facade `activity()` do pacote):

| Método | Uso |
| --- | --- |
| `eventOptions()` / `eventLabel()` / `eventColor()` | badges de evento (Criado/Atualizado/…) |
| `subjectTypeOptions()` / `subjectTypeLabel()` / `subjectLabel()` | filtros e coluna de subject |
| `oldValues()` / `newValues()` / `hasChanges()` | diffs no infolist |

Atributos ocultos no diff: `password`, `remember_token`.

Subjects conhecidos nos filtros: User, Document, Image, Role, Permission. Outros tipos caem no `class_basename`.

## 📝 Logging manual (quando necessário)

Para eventos que não passam por Eloquent save automático:

```php
activity()
    ->performedOn($document)
    ->causedBy($user)
    ->withProperties(['reason' => 'import'])
    ->log('Documento importado');
```

Prefira `LogsActivity` / `HasActivity` no model para CRUD padrão.

## ⚠️ Pitfalls

1. **Policy vendor**: sem mapear `Activity::class` em `AuthServiceProvider`, o Resource quebra autorização.
2. **Relação**: UI e RelationManager usam `activitiesAsSubject`, não um nome inventado.
3. **Somente leitura**: não habilite Create/Edit no Resource — a policy já bloqueia.
4. **Log Viewer / Telescope removidos**: auditoria de domínio fica neste Resource; logs de aplicação continuam em `storage/logs/laravel.log` (ex.: `composer run pail` / `php artisan pail`).
5. **Pulse** continua em `/pulse` com `SystemPermissions::PulseAccess` + gate `viewPulse` — é métricas, não audit trail.

## 🧪 Testes de referência

- `tests/Feature/Filament/ActivityResourceTest.php`
- `tests/Feature/Policies/ActivityPolicyTest.php`
- `tests/Unit/Support/ActivityLogTest.php`
- `tests/Unit/Enums/Permissions/ActivityPermissionsTest.php`
- `tests/Unit/Filament/Actions/ViewActivitiesActionTest.php`

```bash
php artisan test --compact tests/Feature/Filament/ActivityResourceTest.php
php artisan test --compact tests/Feature/Policies/ActivityPolicyTest.php
php artisan test --compact tests/Unit/Support/ActivityLogTest.php
```

## 🔗 Próximos passos

- [Sistema de Permissões](07-sistema-permissoes.md)
- [Policies e Autorização](08-policies-e-autorizacao.md)
- [Actions Customizadas](12-actions-customizadas.md)
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
- [Setup e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
