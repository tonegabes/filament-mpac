<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeMpacModelCommand extends Command
{
    protected $signature = 'make:mpac-model
                            {name : Nome do model, ex: Evento}
                            {--resource= : Base das permissions, ex: eventos}
                            {--migration : Criar migration}
                            {--factory : Criar factory}
                            {--seed : Criar seeder}
                            {--force : Sobrescrever arquivos existentes}';

    protected $description = 'Cria um model com enum de permissions, policy, testes e Filament Resource com view page';

    public function __construct(
        private readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $modelName = Str::studly($this->argument('name'));
        $resourceBase = $this->resolveResourceBase($modelName);
        $modelVariable = Str::camel($modelName);

        $modelCommandOptions = [
            'name' => $modelName,
            '--no-interaction' => true,
        ];

        if ($this->option('migration')) {
            $modelCommandOptions['--migration'] = true;
        }

        if ($this->option('factory')) {
            $modelCommandOptions['--factory'] = true;
        }

        if ($this->option('seed')) {
            $modelCommandOptions['--seed'] = true;
        }

        if ($this->option('force')) {
            $modelCommandOptions['--force'] = true;
        }

        $this->info("Criando model {$modelName}...");

        if ($this->call('make:model', $modelCommandOptions) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->createPermissionEnum($modelName, $resourceBase);
        $this->createPolicy($modelName, $modelVariable);
        $this->createPermissionEnumTest($modelName, $resourceBase);
        $this->createPolicyTest($modelName, $modelVariable, $resourceBase);

        $this->info("Criando Filament Resource {$modelName} com página de visualização...");

        $resourceCommandOptions = [
            'model' => $modelName,
            '--panel' => 'admin',
            '--record-title-attribute' => 'id',
            '--view' => true,
            '--no-interaction' => true,
        ];

        if ($this->option('force')) {
            $resourceCommandOptions['--force'] = true;
        }

        if ($this->call('make:filament-resource', $resourceCommandOptions) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("make:mpac-model finalizado para {$modelName}.");

        return self::SUCCESS;
    }

    private function resolveResourceBase(string $modelName): string
    {
        $resourceOption = (string) ($this->option('resource') ?? '');

        if ($resourceOption !== '') {
            return Str::of($resourceOption)
                ->trim()
                ->lower()
                ->replace(' ', '_')
                ->toString()
            ;
        }

        return Str::of(Str::snake($modelName))
            ->replace(' ', '_')
            ->plural()
            ->toString()
        ;
    }

    private function createPermissionEnum(string $modelName, string $resourceBase): void
    {
        $enumClass = "{$modelName}Permissions";
        $targetPath = app_path("Enums/Permissions/{$enumClass}.php");

        $this->writeFromStub(
            stubPath: 'permission-enum.stub',
            targetPath: $targetPath,
            replacements: [
                '{{ EnumClass }}' => $enumClass,
                '{{ resource }}' => $resourceBase,
            ]
        );
    }

    private function createPolicy(string $modelName, string $modelVariable): void
    {
        $targetPath = app_path("Policies/{$modelName}Policy.php");

        $this->writeFromStub(
            stubPath: 'policy.stub',
            targetPath: $targetPath,
            replacements: [
                '{{ ModelClass }}' => $modelName,
                '{{ modelVariable }}' => $modelVariable,
                '{{ PermissionEnumClass }}' => "{$modelName}Permissions",
            ],
        );
    }

    private function createPermissionEnumTest(string $modelName, string $resourceBase): void
    {
        $enumClass = "{$modelName}Permissions";
        $targetPath = base_path("tests/Unit/Enums/Permissions/{$enumClass}Test.php");

        $this->writeFromStub(
            stubPath: 'permission-enum-test.stub',
            targetPath: $targetPath,
            replacements: [
                '{{ EnumClass }}' => $enumClass,
                '{{ resource }}' => $resourceBase,
            ],
        );
    }

    private function createPolicyTest(string $modelName, string $modelVariable, string $resourceBase): void
    {
        $targetPath = base_path("tests/Feature/Policies/{$modelName}PolicyTest.php");

        $this->writeFromStub(
            stubPath: 'policy-test.stub',
            targetPath: $targetPath,
            replacements: [
                '{{ ModelClass }}' => $modelName,
                '{{ modelVariable }}' => $modelVariable,
                '{{ PermissionEnumClass }}' => "{$modelName}Permissions",
                '{{ resource }}' => $resourceBase,
            ],
        );
    }

    /** @param array<string, string> $replacements */
    private function writeFromStub(string $stubPath, string $targetPath, array $replacements): void
    {
        if (! $this->option('force') && $this->files->exists($targetPath)) {
            $this->warn("Arquivo já existe, pulando: {$targetPath}");

            return;
        }

        $stubContent = $this->files->get(base_path("stubs/mpac/{$stubPath}"));
        $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

        $this->files->ensureDirectoryExists(dirname($targetPath));
        $this->files->put($targetPath, $content);

        $this->line("Arquivo criado: {$targetPath}");
    }
}
