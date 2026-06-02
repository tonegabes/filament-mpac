<?php

declare(strict_types=1);

use App\Enums\Permissions\DocumentPermissions;
use App\Models\Document;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (DocumentPermissions::cases() as $permission) {
        Permission::firstOrCreate(['name' => $permission->value]);
    }
});

it('allows viewAny when user has documents.view.any permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(DocumentPermissions::ViewAny->value);

    expect($user->can('viewAny', Document::class))->toBeTrue();
});

it('allows viewAny when user has documents wildcard permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(DocumentPermissions::All->value);

    expect($user->can('viewAny', Document::class))->toBeTrue();
});

it('denies viewAny when user lacks documents.view.any permission', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Document::class))->toBeFalse();
});

it('allows view when user has documents.view permission', function (): void {
    $user = User::factory()->create();
    $document = Document::create(['name' => 'Documento']);
    $user->givePermissionTo(DocumentPermissions::View->value);

    expect($user->can('view', $document))->toBeTrue();
});

it('allows update when user has documents.update permission', function (): void {
    $user = User::factory()->create();
    $document = Document::create(['name' => 'Documento']);
    $user->givePermissionTo(DocumentPermissions::Update->value);

    expect($user->can('update', $document))->toBeTrue();
});

it('denies update when user lacks documents.update permission', function (): void {
    $user = User::factory()->create();
    $document = Document::create(['name' => 'Documento']);

    expect($user->can('update', $document))->toBeFalse();
});

it('denies view when user lacks documents.view permission', function (): void {
    $user = User::factory()->create();
    $document = Document::create(['name' => 'Documento']);

    expect($user->can('view', $document))->toBeFalse();
});
