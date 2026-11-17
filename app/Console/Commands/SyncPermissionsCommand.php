<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Permission;
use BackedEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Finder\SplFileInfo;

class SyncPermissionsCommand extends Command
{
    private const string PERMISSIONS_ENUM_DIRECTORY = 'Enums/Permissions';

    private const string PERMISSIONS_ENUM_NAMESPACE = 'App\\Enums\\Permissions\\';

    /**
     * @var string
     */
    protected $signature = 'mpac:permissions:sync
        {--prune : Remove permissões do banco que não existem mais nos enums}';

    /**
     * @var string
     */
    protected $description = 'Sincroniza em lote as permissões declaradas nos enums para o banco de dados';

    public function handle(PermissionRegistrar $registrar): int
    {
        $registrar->forgetCachedPermissions();

        $guardName = config()->string('auth.defaults.guard', 'web');
        $permissionNames = $this->discoverPermissionNames();
        $now = now();

        if ($permissionNames !== []) {
            $rows = array_map(
                static fn (string $name): array => [
                    'name' => $name,
                    'guard_name' => $guardName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $permissionNames,
            );

            Permission::upsert($rows, ['name', 'guard_name'], ['updated_at']);
        }

        $prunedCount = 0;

        if ((bool) $this->option('prune')) {
            $prunedCount = Permission::query()
                ->where('guard_name', $guardName)
                ->whereNotIn('name', $permissionNames)
                ->delete();
        }

        $registrar->forgetCachedPermissions();

        $this->info(sprintf(
            'Permissões sincronizadas: %d encontradas%s.',
            count($permissionNames),
            $prunedCount > 0 ? ", {$prunedCount} removidas" : '',
        ));

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function discoverPermissionNames(): array
    {
        $names = [];

        foreach ($this->permissionEnumClasses() as $permissionEnumClass) {
            foreach ($permissionEnumClass::cases() as $permission) {
                $names[] = (string) $permission->value;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<int, class-string<BackedEnum>>
     */
    private function permissionEnumClasses(): array
    {
        $permissionEnumClasses = [];

        foreach (File::allFiles(app_path(self::PERMISSIONS_ENUM_DIRECTORY)) as $file) {
            $class = $this->enumClassFromFile($file);

            if (! enum_exists($class) || ! is_subclass_of($class, BackedEnum::class)) {
                continue;
            }

            /** @var class-string<BackedEnum> $class */
            $permissionEnumClasses[] = $class;
        }

        sort($permissionEnumClasses);

        return $permissionEnumClasses;
    }

    private function enumClassFromFile(SplFileInfo $file): string
    {
        $relativePath = str_replace(
            ['/', '.php'],
            ['\\', ''],
            $file->getRelativePathname(),
        );

        return self::PERMISSIONS_ENUM_NAMESPACE . $relativePath;
    }
}
