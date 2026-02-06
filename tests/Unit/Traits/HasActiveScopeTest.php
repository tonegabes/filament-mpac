<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters active users with scopeIsActive', function (): void {
    User::factory()->count(2)->create(['is_active' => true]);
    User::factory()->count(1)->create(['is_active' => false]);

    $active = User::query()->isActive()->get();

    expect($active)->toHaveCount(2);
});

it('activate sets is_active to true', function (): void {
    $user = User::factory()->inactive()->create();

    expect($user->isActive())->toBeFalse();

    $user->activate();

    expect($user->refresh()->isActive())->toBeTrue();
});

it('deactivate sets is_active to false', function (): void {
    $user = User::factory()->create(['is_active' => true]);

    $user->deactivate();

    expect($user->refresh()->isActive())->toBeFalse();
});

it('isActive returns true when active', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    expect($user->isActive())->toBeTrue();
});

it('isInactive returns true when not active', function (): void {
    $user = User::factory()->inactive()->create();
    expect($user->isInactive())->toBeTrue();
});

it('toggleActive flips state', function (): void {
    $user = User::factory()->create(['is_active' => true]);

    $user->toggleActive();
    expect($user->refresh()->isActive())->toBeFalse();

    $user->toggleActive();
    expect($user->refresh()->isActive())->toBeTrue();
});

it('scopeActiveCount returns count of active records', function (): void {
    User::factory()->count(3)->create(['is_active' => true]);
    User::factory()->count(2)->create(['is_active' => false]);

    $count = User::query()->activeCount();

    expect($count)->toBe(3);
});
