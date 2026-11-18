<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

const MPAC_PACKAGES = [
    'php-quality',
    'laravel-essentials',
    'testing',
    'filament-auth',
    'filament-auth-external',
    'filament-access-control',
    'filament-audit',
    'filament-file-library',
    'filament-branding',
    'filament-core',
];

function mpacPackagesBasePath(string $relativePath = ''): string
{
    $base = 'e:/codes/packages';

    return $relativePath === '' ? $base : $base . '/' . ltrim($relativePath, '/');
}

/**
 * @param  list<array<string, mixed>>  $transcriptEntries
 * @param  array<string, mixed>  $hookPayload
 * @return array<string, mixed>
 */
function runPackageDocsStopHook(array $transcriptEntries, array $hookPayload): array
{
    $tempFile = tempnam(sys_get_temp_dir(), 'mpac-transcript-');
    assert(is_string($tempFile));

    $lines = array_map(
        static fn (array $entry): string => (string) json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        $transcriptEntries,
    );

    file_put_contents($tempFile, implode("\n", $lines) . "\n");

    try {
        $payload = array_merge([
            'terminationReason' => 'NO_TOOL_CALL',
            'continuationActive' => false,
            'transcriptPath' => $tempFile,
        ], $hookPayload);

        $process = new Process(
            command: [PHP_BINARY, base_path('.agents/hooks/check-package-docs.php')],
            cwd: base_path('.agents'),
            input: (string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );

        $process->mustRun();

        $decoded = json_decode(trim($process->getOutput()), true);
        assert(is_array($decoded));

        return $decoded;
    } finally {
        @unlink($tempFile);
    }
}

it('ensures every internal mpac package has complete DX documentation, changelog and agent rules', function (string $package): void {
    $packageDir = mpacPackagesBasePath($package);

    $readmePath = "{$packageDir}/README.md";
    $changelogPath = "{$packageDir}/CHANGELOG.md";
    $agentsPath = "{$packageDir}/AGENTS.md";
    $boostGuidelinePath = "{$packageDir}/resources/boost/guidelines/core.md";
    $docsReadmePath = "{$packageDir}/docs/README.md";
    $docsInstallPath = "{$packageDir}/docs/01-instalacao-e-configuracao.md";
    $docsUsagePath = "{$packageDir}/docs/02-uso-e-exemplos.md";
    $docsArchPath = "{$packageDir}/docs/03-extensibilidade-e-arquitetura.md";

    foreach ([
        $readmePath,
        $changelogPath,
        $agentsPath,
        $boostGuidelinePath,
        $docsReadmePath,
        $docsInstallPath,
        $docsUsagePath,
        $docsArchPath,
    ] as $expectedFile) {
        expect(File::exists($expectedFile))->toBeTrue("Missing expected file: {$expectedFile}");
        expect(trim((string) File::get($expectedFile)))->not->toBeEmpty("Empty file: {$expectedFile}");
    }

    $readme = (string) File::get($readmePath);
    $changelog = (string) File::get($changelogPath);
    $agents = (string) File::get($agentsPath);
    $boostGuideline = (string) File::get($boostGuidelinePath);

    expect($readme)
        ->toContain("# mpac/{$package}")
        ->toContain('Instalação Rápida')
        ->toContain('Configuração')
        ->toContain('docs/README.md')
        ->toContain('CHANGELOG.md');

    expect($changelog)
        ->toContain('Keep a Changelog')
        ->toContain('## [Unreleased]')
        ->toContain('## [1.0.0] - 2026-11-18');

    expect($agents)
        ->toContain('CHANGELOG.md')
        ->toContain('docs/');

    expect($boostGuideline)
        ->toContain('CHANGELOG.md')
        ->toContain('docs/');
})->with(MPAC_PACKAGES);

it('ensures global indexes, Boost governance rules, skills and stop hooks exist', function (): void {
    $expectedPaths = [
        mpacPackagesBasePath('README.md'),
        mpacPackagesBasePath('AGENTS.md'),
        mpacPackagesBasePath('.agents/skills/mpac-package-docs/SKILL.md'),
        mpacPackagesBasePath('.agents/hooks.json'),
        mpacPackagesBasePath('.agents/hooks/check-package-docs.php'),
        base_path('docs/19-pacotes-internos-mpac.md'),
        base_path('.ai/guidelines/mpac-packages-governance.md'),
        base_path('AGENTS.md'),
        base_path('.agents/skills/mpac-package-docs/SKILL.md'),
        base_path('.cursor/skills/mpac-package-docs/SKILL.md'),
        base_path('.agents/hooks.json'),
        base_path('.agents/hooks/check-package-docs.php'),
    ];

    foreach ($expectedPaths as $path) {
        expect(File::exists($path))->toBeTrue("Missing governance file: {$path}");
    }

    expect((string) File::get(base_path('AGENTS.md')))
        ->toContain('mpac-packages-governance');

    expect((string) File::get(base_path('docs/README.md')))
        ->toContain('19-pacotes-internos-mpac.md');
});

it('forces agent continuation when package code is modified without updating CHANGELOG.md', function (): void {
    $result = runPackageDocsStopHook([
        [
            'step_index' => 0,
            'type' => 'USER_INPUT',
            'content' => 'Altere o utilitario Arr',
        ],
        [
            'step_index' => 1,
            'type' => 'PLANNER_RESPONSE',
            'tool_calls' => [
                [
                    'name' => 'edit_file',
                    'args' => [
                        'Path' => 'e:/codes/packages/laravel-essentials/src/Support/Arr.php',
                    ],
                ],
            ],
        ],
    ], []);

    expect($result['decision'] ?? null)->toBe('continue')
        ->and((string) ($result['reason'] ?? ''))->toContain('laravel-essentials')
        ->and((string) ($result['reason'] ?? ''))->toContain('CHANGELOG.md');
});

it('allows stop when package code and CHANGELOG.md are both updated in the same turn', function (): void {
    $result = runPackageDocsStopHook([
        [
            'step_index' => 0,
            'type' => 'USER_INPUT',
            'content' => 'Altere o utilitario Arr e documente',
        ],
        [
            'step_index' => 1,
            'type' => 'PLANNER_RESPONSE',
            'tool_calls' => [
                [
                    'name' => 'edit_file',
                    'args' => [
                        'Path' => '"e:\\\\codes\\\\packages\\\\laravel-essentials\\\\src\\\\Support\\\\Arr.php"',
                    ],
                ],
                [
                    'name' => 'edit_file',
                    'args' => [
                        'Path' => '"e:\\\\codes\\\\packages\\\\laravel-essentials\\\\CHANGELOG.md"',
                    ],
                ],
            ],
        ],
    ], []);

    expect($result['decision'] ?? null)->toBe('allow');
});

it('allows stop when continuationActive is already true to prevent infinite loops', function (): void {
    $result = runPackageDocsStopHook([
        [
            'step_index' => 0,
            'type' => 'USER_INPUT',
            'content' => 'Altere o utilitario Arr',
        ],
        [
            'step_index' => 1,
            'type' => 'PLANNER_RESPONSE',
            'tool_calls' => [
                [
                    'name' => 'edit_file',
                    'args' => [
                        'Path' => 'e:/codes/packages/laravel-essentials/src/Support/Arr.php',
                    ],
                ],
            ],
        ],
    ], [
        'continuationActive' => true,
    ]);

    expect($result['decision'] ?? null)->toBe('allow');
});
