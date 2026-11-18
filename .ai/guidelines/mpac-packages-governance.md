# Governança dos Pacotes Internos MPAC (`mpac/*`)

Este projeto consome os 10 pacotes internos localizados em `e:\codes\packages\*`:

- `mpac/php-quality` (`e:\codes\packages\php-quality`)
- `mpac/laravel-essentials` (`e:\codes\packages\laravel-essentials`)
- `mpac/testing` (`e:\codes\packages\testing`)
- `mpac/filament-auth` (`e:\codes\packages\filament-auth`)
- `mpac/filament-auth-external` (`e:\codes\packages\filament-auth-external`)
- `mpac/filament-access-control` (`e:\codes\packages\filament-access-control`)
- `mpac/filament-audit` (`e:\codes\packages\filament-audit`)
- `mpac/filament-file-library` (`e:\codes\packages\filament-file-library`)
- `mpac/filament-branding` (`e:\codes\packages\filament-branding`)
- `mpac/filament-core` (`e:\codes\packages\filament-core`)

## Regra Obrigatória ao Editar Qualquer Pacote Interno

Ao modificar qualquer código em `src/`, `config/`, `database/`, `resources/`, `routes/`, arquivos de qualidade ou `composer.json` de um pacote em `e:\codes\packages\<pacote>\`, você **DEVE no mesmo turno**:

1. **Atualizar `<pacote>/CHANGELOG.md`** na seção `## [Unreleased]` (usando Keep a Changelog: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`).
2. **Revisar e atualizar `<pacote>/README.md` e `<pacote>/docs/*.md`** sempre que a alteração afetar API pública, opções de plugin, configurações, comandos Artisan, traits, contratos, eventos, migrations ou rotas.
3. **Executar os checks de qualidade** no pacote modificado (`composer lint`, `composer analyse`, `composer test`) e no app template (`composer lint`, `composer analyze`, `composer test`).

> Mesmo que as regras gerais mencionem não criar arquivos de documentação avulsos sem pedido do usuário, **manter `CHANGELOG.md`, `README.md` e `docs/` dos pacotes `mpac/*` sincronizados com alterações de código é obrigatório**.
