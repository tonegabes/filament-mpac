# Logs de Atividade

Guia do sistema de auditoria baseado em `spatie/laravel-activitylog` (^5): o que é registrado, como aparece no painel Filament e como estender com segurança.

## 📚 Visão geral

| Camada | Onde | Papel |
| --- | --- | --- |
| Persistência | `Spatie\Activitylog\Models\Activity` | Registro de evento (assunto, causer, `attribute_changes`) |
| Model logging | traits `LogsActivity` / `HasActivity` | Grava create/update/delete automaticamente |
| UI global | `ActivityResource` | Listagem e detalhe em **Ferramentas → Logs de atividade** |
| UI por registro | `ViewActivitiesAction` | Slide-over “Histórico” nas tabelas |
| UI na view | `ActivitiesRelationManager` | Aba/relação “Histórico” (somente leitura) |
| Apresentação | `App\Support\ActivityLog` | Labels, cores, filtros e formatação de diffs |
| Autorização | `ActivityPermissions` + `ActivityPolicy` | Somente leitura (`viewAny` / `view`) |

Não há create/edit/delete de atividades pelo painel: a policy retorna `false` para mutações.

## 🏗️ Arquitetura

```text
Model (LogsActivity | HasActivity)
        │  eventos Eloquent
        ▼
Activity (subject + causer + attribute_changes)
        │
        ├── ActivityResource (lista / view)
        ├── ViewActivitiesAction (últimas 30 do subject)
        └── ActivitiesRelationManager (tabela do subject)
                 │
                 └── App\Support\ActivityLog (labels / diffs)
```

Navegação: `NavGroups::Tools`, sort `10`, ícone `Phosphor::ClockCounterClockwise`.

## 🔐 Permissões

Enum `App\Enums\Permissions\ActivityPermissions`:

| Case | Valor |
| --- | --- |
| `All` | `activities.*` |
| `ViewAny` | `activities.view.any` |
| `View` | `activities.view` |

Distribuição no `RoleSeeder`:

| Role | Acesso a atividades |
| --- | --- |
| `Developer` | `*` (inclui activities) |
| `Admin` | `ActivityPermissions::All` |
| `User` | nenhum |

A policy está registrada manualmente em `AuthServiceProvider` porque o model é do vendor (`Activity::class` → `ActivityPolicy`).

```php
// app/Providers/AuthServiceProvider.php
protected $policies = [
    Activity::class => ActivityPolicy::class,
    // ...
];
```

## 🧩 Models que registram atividade

| Model | Trait | Atributos logados |
| --- | --- | --- |
| `User` | `HasActivity` | `name`, `username`, `email`, `is_active` (+ `dontLogEmptyChanges`) |
| `Document` | `LogsActivity` | `name` |
| `Image` | `LogsActivity` | `$this->fillable` (`name`) |
| `Role` | `LogsActivity` | `name`, `guard_name` |
| `Permission` | `LogsActivity` | `name`, `guard_name` |

### `HasActivity` vs `LogsActivity` (v5)

- **`LogsActivity`**: model é **assunto** (`activitiesAsSubject()`). Use em entidades de domínio.
- **`HasActivity`**: combina logging + causer. Use em `User` (pode ser assunto e quem causou a ação).
- Relação v5: preferir `activitiesAsSubject()` / `activitiesAsCauser()` (não a API antiga `activities` / `actions` do v4).
- Diffs: use `$activity->attribute_changes` (não `changes()` do v4).

Exemplo mínimo:

```php
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Document extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty();
    }
}
```

## 🖥️ UI Filament

### ActivityResource

- Páginas: `index` (`ListActivities`) e `view` (`ViewActivity`) — sem create/edit.
- Query eager-load: `causer`, `subject`.
- Tabela (`ActivitiesTable`): data, evento (badge), assunto, usuário, descrição (toggleable), filtros de evento/tipo/data.
- Infolist (`ActivityInfolist`): detalhes + seção “Alterações” (Antes/Depois) quando há diffs.

### ViewActivitiesAction

`app/Filament/Actions/ViewActivitiesAction.php`

- Nome: `activities`; label **Histórico**; slide-over somente leitura.
- Visível se o usuário passa em `viewAny` em `Activity` **e** o record tem `activitiesAsSubject`.
- Carrega até **30** atividades recentes do subject (com `causer`).

Já integrada nas tabelas de: Users, Documents, Images, Roles, Permissions.

```php
->recordActions([
    // ...
    ViewActivitiesAction::make(),
])
```

### ActivitiesRelationManager

`app/Filament/RelationManagers/ActivitiesRelationManager.php`

- Relação: `activitiesAsSubject`
- `isReadOnly(): true`
- Reusa `ActivitiesTable::configure($table, forSubject: true)` (oculta coluna/filtro de assunto)

Registrado em: `UserResource`, `DocumentResource`, `ImageResource`, `RoleResource`, `PermissionResource`.

## 🛠️ App\Support\ActivityLog

Helper de apresentação (não grava logs):

| Método | Uso |
| --- | --- |
| `eventOptions()` / `eventLabel()` / `eventColor()` | Filtros e badges (Criado/Atualizado/Excluído/Restaurado) |
| `subjectTypeOptions()` / `subjectTypeLabel()` / `subjectLabel()` | Labels de User, Document, Image, Role, Permission |
| `oldValues()` / `newValues()` / `hasChanges()` | Diffs a partir de `attribute_changes` |

Restrições:

- Atributos `password` e `remember_token` são **ocultados** na UI.
- Tipos de assunto no filtro/lista são os mapeados em `subjectTypeOptions()`; outros classes caem para `class_basename`.
- Valores booleanos viram `Sim`/`Não`; arrays/objetos viram JSON.

Ao adicionar um novo model com logging, atualize `subjectTypeOptions()` se quiser label amigável no resource global.

## ➕ Como habilitar histórico em um novo Resource

1. No model: `LogsActivity` (ou `HasActivity` se também for causer) + `getActivitylogOptions()`.
2. Na table: `ViewActivitiesAction::make()` em `recordActions`.
3. No Resource: `ActivitiesRelationManager::class` em `getRelations()` (páginas com view/edit que suportem relation managers).
4. Se for um tipo novo para o catálogo global: incluir em `ActivityLog::subjectTypeOptions()`.
5. Permissões já cobrem leitura via `activities.*`; Admin/Developer já têm acesso após seed.
6. Testes: policy + smoke do resource (ver `tests/Feature/Filament/ActivityResourceTest.php`).

## ⚠️ Pitfalls

1. **Sem seed**: menus/ações somem — rode `migrate --seed` ou sincronize roles após criar `ActivityPermissions`.
2. **Policy do vendor model**: sem registro em `AuthServiceProvider`, Filament não aplica `ActivityPolicy`.
3. **Action invisível**: falta `activitiesAsSubject` (trait de logging) ou permissão `activities.view.any`.
4. **API v4**: `changes()`, `dontSubmitEmptyLogs()`, `withoutLogs()` foram renomeados na v5.
5. **Segredos**: nunca inclua `password` / tokens em `logOnly()`; a UI já esconde, mas o banco não deve receber o valor.
6. **Causer “Sistema”**: placeholder na UI quando não há usuário autenticado no momento do log.

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
