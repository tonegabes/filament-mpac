<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use BackedEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Finder\SplFileInfo;

final class PermissionSeeder extends Seeder
{
    private const string PERMISSIONS_ENUM_DIRECTORY = 'Enums/Permissions';

    private const string PERMISSIONS_ENUM_NAMESPACE = 'App\\Enums\\Permissions\\';

    /**
     * Seed all permissions declared as enums.
     */
    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissionCases() as $permission) {
            Permission::firstOrCreate(['name' => (string) $permission->value]);
        }
    }

    /**
     * @return array<int, BackedEnum>
     */
    private function permissionCases(): array
    {
        $permissions = [];

        foreach ($this->permissionEnumClasses() as $permissionEnumClass) {
            foreach ($permissionEnumClass::cases() as $permission) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
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

        return array_values($permissionEnumClasses);
    }

    /**
     * Build the enum class name matching the given file.
     */
    private function enumClassFromFile(SplFileInfo $file): string
    {
        $relativePath = str_replace(
            ['/', '.php'],
            ['\\', ''],
            $file->getRelativePathname(),
        );

        return self::PERMISSIONS_ENUM_NAMESPACE.$relativePath;
    }
}
