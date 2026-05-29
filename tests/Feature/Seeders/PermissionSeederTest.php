<?php

declare(strict_types=1);

use App\Enums\Permissions\PanelPermissions;
use App\Enums\Permissions\PermissionPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\SystemPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

afterEach(function (): void {
    File::delete(app_path('Enums/Permissions/GeneratedSeederPermissions.php'));
});

it('creates all permissions from enums', function (): void {
    $this->seed(Database\Seeders\PermissionSeeder::class);

    $expected = array_merge(
        array_map(fn ($c) => $c->value, PanelPermissions::cases()),
        array_map(fn ($c) => $c->value, SystemPermissions::cases()),
        array_map(fn ($c) => $c->value, UserPermissions::cases()),
        array_map(fn ($c) => $c->value, RolePermissions::cases()),
        array_map(fn ($c) => $c->value, PermissionPermissions::cases()),
    );

    foreach ($expected as $name) {
        $this->assertDatabaseHas(Permission::class, ['name' => $name]);
    }

    expect(Permission::count())->toBe(count($expected));
});

it('discovers permission enums automatically', function (): void {
    File::put(app_path('Enums/Permissions/GeneratedSeederPermissions.php'), <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

enum GeneratedSeederPermissions: string
{
    case View = 'generated.view';
}
PHP);

    $this->seed(Database\Seeders\PermissionSeeder::class);

    $this->assertDatabaseHas(Permission::class, ['name' => 'generated.view']);
});
