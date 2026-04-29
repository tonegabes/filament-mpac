<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Images\Pages\ListImages;
use App\Filament\Resources\Images\Pages\ViewImage;
use App\Models\Image;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

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

function createImageRecord(string $name, string $fileName): Image
{
    $image = Image::create(['name' => $name]);

    $image
        ->addMedia(UploadedFile::fake()->image($fileName))
        ->toMediaCollection(Image::COLLECTION_NAME);

    return $image->refresh();
}

it('can render list images page and see records', function (): void {
    $image = createImageRecord('Main brand image', 'brand.png');

    Livewire::test(ListImages::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$image]);
});

it('can search images by name', function (): void {
    $visible = createImageRecord('Visible image', 'visible.png');
    $hidden = createImageRecord('Hidden image', 'hidden.png');

    Livewire::test(ListImages::class)
        ->searchTable('Visible')
        ->assertCanSeeTableRecords([$visible])
        ->assertCanNotSeeTableRecords([$hidden]);
});

it('can render the image view page', function (): void {
    $image = createImageRecord('Viewable image', 'viewable.png');

    Livewire::test(ViewImage::class, ['record' => $image->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => 'Viewable image',
        ]);
});

it('can delete an image from the table', function (): void {
    $image = createImageRecord('Disposable image', 'disposable.png');

    Livewire::test(ListImages::class)
        ->callAction(TestAction::make(DeleteAction::class)->table($image))
        ->assertNotified()
        ->assertCanNotSeeTableRecords([$image]);

    $this->assertDatabaseMissing(Image::class, [
        'id' => $image->id,
    ]);
});
