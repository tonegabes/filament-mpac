# Documentação do Sistema

Bem-vindo à documentação do Filament MPAC. Use estes guias para entender a arquitetura e desenvolver features no padrão do projeto.

## 📚 Índice

### Fundamentos

1. [Estrutura do Projeto](01-estrutura-do-projeto.md)
   - Arquitetura geral
   - Organização de pastas
   - Convenções de nomenclatura

### Filament Resources

2. [Criando Recursos Filament](02-criando-recursos-filament.md)
   - Como criar um Resource completo
   - Estrutura de pastas
   - Configuração básica

3. [Schemas e Formulários](03-schemas-e-formularios.md)
   - Schemas separados (Form, Table, Infolist)
   - Componentes de formulário
   - Relacionamentos e validação

4. [Tabelas](04-tabelas.md)
   - Configuração de tabelas
   - Colunas, filtros e busca
   - Ações de tabela

### Páginas e Componentes

5. [Páginas Customizadas](05-paginas-customizadas.md)
   - SettingsPage
   - Login/registro (local e LDAP)
   - ViewRecord

6. [Componentes Customizados](06-componentes-customizados.md)
   - PanelSwitcher
   - Como criar campos de formulário

### Autorização e Permissões

7. [Sistema de Permissões](07-sistema-permissoes.md)
   - Spatie Laravel Permission
   - Enums de Permissões
   - Roles (`UserRole`)

8. [Policies e Autorização](08-policies-e-autorizacao.md)
   - Criando Policies
   - Integração com Filament
   - Métodos de autorização

### Recursos do Laravel

9. [Enums e Convenções](09-enums-e-convencoes.md)
   - NavGroups, Panels, UserRole
   - Enums de permissões
   - Convenções de uso

10. [Traits](10-traits.md)
    - HasIsActiveScope
    - HasNotifications
    - BetterEnum

11. [Settings](11-settings.md)
    - Spatie Laravel Settings
    - Classes de Settings
    - Páginas de configuração

12. [Actions Customizadas](12-actions-customizadas.md)
    - Criando Actions
    - CopyFileUrlAction
    - Integração com Livewire

### Testes e Modelos

13. [Testes](13-testes.md)
    - Testes para Filament Resources
    - Testes de formulários e tabelas
    - Exemplos com Pest

14. [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md)
    - Convenções de Models
    - Relacionamentos Eloquent
    - Media Library e Activity Log

### Configuração e Operação

15. [Panel Provider](15-panel-provider.md)
    - AdminPanelProvider
    - Descoberta automática
    - Grupos de navegação

16. [Exemplos Completos](16-exemplos-completos.md)
    - Exemplo completo: Resource do zero
    - Exemplo completo: Página de configurações
    - Fluxo completo de desenvolvimento

17. [Setup, Dependências e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
    - Setup local
    - Upgrade de dependências
    - Auth local/LDAP e pitfalls

## 🚀 Guia Rápido

### Para começar a desenvolver:

1. **Setup**: [Setup, Dependências e Troubleshooting](17-setup-dependencias-e-troubleshooting.md)
2. **Leia**: [Estrutura do Projeto](01-estrutura-do-projeto.md)
3. **Criar um Resource**: [Criando Recursos Filament](02-criando-recursos-filament.md)
4. **Configurar formulários**: [Schemas e Formulários](03-schemas-e-formularios.md)
5. **Adicionar permissões**: [Sistema de Permissões](07-sistema-permissoes.md)
6. **Escrever testes**: [Testes](13-testes.md)

### Scaffold completo de recurso (MPAC)

```bash
php artisan make:mpac-model Evento --resource=eventos
```

Esse comando gera:

- Model
- Enum de permissões
- Policy
- Teste unitário do enum de permissões
- Teste de feature da policy
- Filament Resource com página `View`

Opções:

```bash
php artisan make:mpac-model Evento --resource=eventos --migration --factory --seed
php artisan make:mpac-model Evento --resource=eventos --force
```

### Convenções Importantes

- ✅ Sempre use **Phosphor Icons** (não Hero Icons)
- ✅ Use **strict types** em todos os arquivos PHP
- ✅ Separe Schemas em classes próprias
- ✅ Use **type hints** explícitos
- ✅ Escreva **testes** para novas features
- ✅ Use **semantic commits** em inglês

## 📖 Versões Utilizadas

- **PHP**: `^8.3` (ambiente atual em 8.4)
- **Laravel**: v13
- **Filament**: v5
- **Livewire**: v4
- **Pest**: v5
- **PHPUnit**: v13
- **Tailwind CSS**: v4
- **ldaprecord-laravel**: v4
- **spatie/laravel-permission**: v8

## 🧩 Funcionalidades do Projeto Atual

- Autenticação com modos **Local** e **LDAP**
- Painéis **app** e **admin** com `PanelSwitcher`
- Recursos de arquivos: **Document**, **Image** e **Media**
- Configurações do sistema com **Spatie Settings**
- Permissões e roles com **Spatie Permission** (`UserRole`: Developer, Admin, User)
- Logs de atividade com **Spatie Activitylog**

Veja também:

- [Páginas Customizadas](05-paginas-customizadas.md) para autenticação e SettingsPage
- [Panel Provider](15-panel-provider.md) para configuração do painel admin
- [Modelos e Relacionamentos](14-modelos-e-relacionamentos.md) para biblioteca de arquivos e media

## 🔗 Links Úteis

- [Documentação Oficial do Filament](https://filamentphp.com/docs)
- [Documentação do Laravel](https://laravel.com/docs)
- [Documentação do Livewire](https://livewire.laravel.com/docs)

## 📝 Contribuindo

Ao adicionar novas features, certifique-se de:

1. Seguir as convenções documentadas
2. Atualizar esta documentação se necessário
3. Escrever testes para novas funcionalidades
4. Usar semantic commits

---

**Última atualização**: Julho 2026
