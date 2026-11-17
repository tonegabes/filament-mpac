<?php

declare(strict_types=1);

use App\Enums\Permissions\UserPermissions;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

it('syncs enum permissions and prunes obsolete ones when requested', function (): void {
    Permission::firstOrCreate(['name' => 'obsolete.permission', 'guard_name' => 'web']);

    $sync = artisan('mpac:permissions:sync');
    assert($sync instanceof PendingCommand);
    $sync->assertSuccessful()->run();

    expect(Permission::where('name', UserPermissions::ViewAny->value)->exists())->toBeTrue()
        ->and(Permission::where('name', 'obsolete.permission')->exists())->toBeTrue();

    $prune = artisan('mpac:permissions:sync', ['--prune' => true]);
    assert($prune instanceof PendingCommand);
    $prune->assertSuccessful()->run();

    expect(Permission::where('name', UserPermissions::ViewAny->value)->exists())->toBeTrue()
        ->and(Permission::where('name', 'obsolete.permission')->exists())->toBeFalse();
});
