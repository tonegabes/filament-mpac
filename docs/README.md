# Documentação do Sistema

Bem-vindo à documentação completa do sistema Filament MPAC. Esta documentação foi criada para facilitar o entendimento e desenvolvimento de novas features seguindo as convenções estabelecidas no projeto.

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
   - Páginas de autenticação
   - ViewRecord

6. [Componentes Customizados](06-componentes-customizados.md)
   - Criando componentes de formulário
   - ImagePicker e IconPicker
   - Views Blade

### Autorização e Permissões

7. [Sistema de Permissões](07-sistema-permissoes.md)
   - Spatie Laravel Permission
   - Enums de Permissões
   - Verificação de permissões

8. [Policies e Autorização](08-policies-e-autorizacao.md)
   - Criando Policies
   - Integração com Filament
   - Métodos de autorização

### Recursos do Laravel

9. [Enums e Convenções](09-enums-e-convencoes.md)
   - NavGroups
   - Enums de permissões
   - Convenções de uso

10. [Traits](10-traits.md)
    - HasActiveScope
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

### Configuração

15. [Panel Provider](15-panel-provider.md)
    - AdminPanelProvider
    - Descoberta automática
    - Grupos de navegação

16. [Exemplos Completos](16-exemplos-completos.md)
    - Exemplo completo: Resource do zero
    - Exemplo completo: Página de configurações
    - Fluxo completo de desenvolvimento

## 🚀 Guia Rápido

### Para começar a desenvolver:

1. **Leia primeiro**: [Estrutura do Projeto](01-estrutura-do-projeto.md)
2. **Criar um Resource**: [Criando Recursos Filament](02-criando-recursos-filament.md)
3. **Configurar formulários**: [Schemas e Formulários](03-schemas-e-formularios.md)
4. **Adicionar permissões**: [Sistema de Permissões](07-sistema-permissoes.md)
5. **Escrever testes**: [Testes](13-testes.md)

### Convenções Importantes

- ✅ Sempre use **Phosphor Icons** (não Hero Icons)
- ✅ Use **strict types** em todos os arquivos PHP
- ✅ Separe Schemas em classes próprias
- ✅ Use **type hints** explícitos
- ✅ Escreva **testes** para novas features
- ✅ Use **semantic commits** em inglês

## 📖 Versões Utilizadas

- **PHP**: 8.4.11
- **Laravel**: v12
- **Filament**: v4
- **Livewire**: v3
- **Pest**: v3
- **Tailwind CSS**: v4

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

**Última atualização**: Fevereiro 2026
