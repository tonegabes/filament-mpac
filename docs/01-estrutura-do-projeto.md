# Estrutura do Projeto

Este documento descreve a arquitetura geral do projeto, organização de pastas e convenções de nomenclatura.

## 📁 Arquitetura Geral

O projeto segue uma **arquitetura modular Filament** com separação clara de responsabilidades:

```
app/
├── Contracts/              # Interfaces e contratos
├── Enums/                  # Enumeradores (NavGroups, Permissions, etc.)
├── Filament/               # Recursos Filament
│   ├── Actions/            # Ações customizadas
│   ├── Components/         # Componentes de formulário customizados
│   ├── Pages/              # Páginas customizadas (Auth, Settings)
│   └── Resources/          # Resources CRUD
│       └── {Entity}/
│           ├── Pages/      # Páginas do Resource (List, Create, Edit, View)
│           ├── Schemas/    # Schemas separados (Form, Table, Infolist)
│           └── Tables/     # Configuração de tabelas
├── Http/                   # Controllers e Responses HTTP
├── Livewire/               # Componentes Livewire
├── Models/                 # Modelos Eloquent
├── Notifications/           # Notificações do sistema
├── Policies/               # Policies de autorização
├── Providers/              # Service Providers
│   └── Filament/           # Providers específicos do Filament
├── Services/                # Serviços de negócio
├── Settings/                # Classes de configurações (Spatie Settings)
└── Traits/                  # Traits reutilizáveis
```

## 🎯 Convenções de Nomenclatura

### Classes PHP

- **PascalCase** para nomes de classes: `UserResource`, `DocumentForm`
- **camelCase** para métodos: `getNavigationGroup()`, `configure()`
- **snake_case** para propriedades de banco de dados: `is_active`, `created_at`

### Arquivos

- Nomes de arquivos seguem o nome da classe
- Um arquivo = uma classe
- Namespace reflete a estrutura de pastas

### Pastas de Resources

Para cada Resource, a estrutura padrão é:

```
app/Filament/Resources/{Entity}/
├── {Entity}Resource.php          # Classe principal do Resource
├── Pages/
│   ├── List{Entity}.php         # Página de listagem
│   ├── Create{Entity}.php       # Página de criação
│   ├── Edit{Entity}.php         # Página de edição
│   └── View{Entity}.php         # Página de visualização (opcional)
├── Schemas/
│   ├── {Entity}Form.php         # Schema do formulário
│   ├── {Entity}Table.php        # Schema da tabela (ou em Tables/)
│   └── {Entity}Infolist.php     # Schema do infolist (opcional)
└── Tables/
    └── {Entity}Table.php         # Configuração da tabela
```

**Exemplo**: `UserResource` → `app/Filament/Resources/Users/`

## 📋 Padrões PSR Seguidos

### PSR-1: Basic Coding Standard

- Classes em PascalCase
- Métodos em camelCase
- Constantes em UPPER_CASE
- Arquivos devem conter apenas uma classe

### PSR-12: Extended Coding Style

- 4 espaços para indentação
- Chaves na mesma linha para classes/métodos
- Chaves na linha seguinte para estruturas de controle
- Linha em branco após namespace e use statements

### Strict Types

**Sempre** use `declare(strict_types=1);` no início de cada arquivo PHP:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;
```

### Type Hints

**Sempre** use type hints explícitos:

```php
public function configure(Schema $schema): Schema
{
    return $schema->components([...]);
}
```

## 🔧 Estrutura de Schemas Separados

Uma das principais convenções do projeto é **separar Schemas em classes próprias**:

### Antes (não recomendado)

```php
// UserResource.php
public static function form(Schema $schema): Schema
{
    return $schema->components([
        TextInput::make('name'),
        // ... muitos componentes
    ]);
}
```

### Depois (recomendado)

```php
// UserResource.php
public static function form(Schema $schema): Schema
{
    return UserForm::configure($schema);
}

// UserForm.php
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name'),
            // ...
        ]);
    }
}
```

**Vantagens**:
- Melhor organização
- Reutilização de schemas
- Código mais limpo e testável
- Facilita manutenção

## 🎨 Ícones

**Sempre use Phosphor Icons**, nunca Hero Icons:

```php
use ToneGabes\Filament\Icons\Enums\Phosphor;

protected static string|BackedEnum|null $navigationIcon = Phosphor::Users;
```

## 📝 PHPDoc

Documente métodos e propriedades importantes:

```php
/**
 * Configure the form schema.
 *
 * @param Schema $schema
 * @return Schema
 */
public static function configure(Schema $schema): Schema
{
    // ...
}
```

## 🔗 Próximos Passos

- [Criando Recursos Filament](02-criando-recursos-filament.md) - Aprenda a criar um Resource completo
- [Schemas e Formulários](03-schemas-e-formularios.md) - Entenda como estruturar formulários
