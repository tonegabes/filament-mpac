<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Pages\ViewActivity;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('can render list activities page and see records', function (): void {
    $subject = User::factory()->create(['name' => 'Logged Subject']);

    $activities = Activity::query()
        ->where('subject_type', $subject->getMorphClass())
        ->where('subject_id', $subject->id)
        ->get();

    expect($activities)->not->toBeEmpty();

    Livewire::test(ListActivities::class)
        ->assertOk()
        ->assertCanSeeTableRecords($activities);
});

it('can render the activity view page', function (): void {
    $subject = User::factory()->create(['name' => 'Viewable Subject']);

    $activity = Activity::query()
        ->where('subject_type', $subject->getMorphClass())
        ->where('subject_id', $subject->id)
        ->firstOrFail();

    Livewire::test(ViewActivity::class, ['record' => $activity->getRouteKey()])
        ->assertOk()
        ->assertSee('Viewable Subject');
});

it('can open activity history from the users table', function (): void {
    $user = User::factory()->create(['name' => 'Timeline User']);

    Livewire::test(ListUsers::class)
        ->callAction(TestAction::make('activities')->table($user))
        ->assertSee('Timeline User');
});

it('does not record password updates on the user activity log', function (): void {
    $user = User::factory()->create();

    $user->update([
        'name' => 'Updated Without Secret',
        'password' => 'new-password',
    ]);

    $activity = Activity::query()
        ->where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    $attributes = $activity->attribute_changes?->get('attributes') ?? [];
    $old = $activity->attribute_changes?->get('old') ?? [];

    expect($attributes)->toHaveKey('name')
        ->and($attributes)->not->toHaveKey('password')
        ->and($old)->not->toHaveKey('password');
});

it('denies list access when user role lacks activities permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::User->value);

    $this->actingAs($user);

    Livewire::test(ListActivities::class)
        ->assertForbidden();
});
