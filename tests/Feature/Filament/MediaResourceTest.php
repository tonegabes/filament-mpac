<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Media\Pages\ViewMedia;
use App\Models\Image;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $this->seed(Database\Seeders\PermissionSeeder::class);
    $this->seed(Database\Seeders\RoleSeeder::class);

    Storage::fake(Image::COLLECTION_NAME);

    $user = User::factory()->create();
    $user->assignRole(Roles::Developer->value);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function createMediaRecord(string $name, string $fileName): Media
{
    $image = Image::create(['name' => $name]);

    $image
        ->addMedia(UploadedFile::fake()->image($fileName))
        ->toMediaCollection(Image::COLLECTION_NAME);

    return $image->getFirstMedia(Image::COLLECTION_NAME);
}

it('can render list media page and see records', function (): void {
    $media = createMediaRecord('Gallery image', 'gallery.png');

    Livewire::test(ListMedia::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$media]);
});

it('can search media by name', function (): void {
    $visible = createMediaRecord('Press release image', 'press-release.png');
    $hidden = createMediaRecord('Internal image', 'internal.png');

    Livewire::test(ListMedia::class)
        ->searchTable('press-release')
        ->assertCanSeeTableRecords([$visible])
        ->assertCanNotSeeTableRecords([$hidden]);
});

it('can render the media view page', function (): void {
    $media = createMediaRecord('Viewable media', 'viewable.png');

    Livewire::test(ViewMedia::class, ['record' => $media->getRouteKey()])
        ->assertOk();
});

it('can delete media from the table', function (): void {
    $media = createMediaRecord('Disposable media', 'disposable.png');

    Livewire::test(ListMedia::class)
        ->callAction(TestAction::make(DeleteAction::class)->table($media))
        ->assertNotified()
        ->assertCanNotSeeTableRecords([$media]);

    $this->assertDatabaseMissing(Media::class, [
        'id' => $media->id,
    ]);
});
