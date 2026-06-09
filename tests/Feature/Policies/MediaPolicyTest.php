<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

it('allows viewAny for authenticated users', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Media::class))->toBeTrue();
});

it('allows deleting media for authenticated users', function (): void {
    $user = User::factory()->create();
    $media = new Media;

    expect($user->can('delete', $media))->toBeTrue();
});

it('allows bulk deleting media for authenticated users', function (): void {
    $user = User::factory()->create();

    expect($user->can('deleteAny', Media::class))->toBeTrue();
});
