<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Media\Pages\ViewMedia;
use App\Models\Document;
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
    Storage::fake(Document::COLLECTION_NAME);

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

function createDocumentMediaRecord(string $name, string $fileName): Media
{
    $document = Document::create(['name' => $name]);

    $document
        ->addMedia(UploadedFile::fake()->createWithContent($fileName, "%PDF-1.4\n% Test PDF\n"))
        ->toMediaCollection(Document::COLLECTION_NAME);

    return $document->getFirstMedia(Document::COLLECTION_NAME);
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

it('can filter media library by collection and file type', function (): void {
    $image = createMediaRecord('Visible library image', 'visible-library.png');
    $document = createDocumentMediaRecord('Visible library document', 'visible-library.pdf');

    Livewire::test(ListMedia::class)
        ->filterTable('collection_name', Image::COLLECTION_NAME)
        ->assertCanSeeTableRecords([$image])
        ->assertCanNotSeeTableRecords([$document])
        ->removeTableFilter('collection_name')
        ->filterTable('documents')
        ->assertCanSeeTableRecords([$document])
        ->assertCanNotSeeTableRecords([$image]);
});

it('shows friendly media metadata in the library table', function (): void {
    $media = createMediaRecord('Readable image', 'readable.png');

    Livewire::test(ListMedia::class)
        ->assertCanSeeTableRecords([$media])
        ->assertSee('Imagens')
        ->assertSee('Imagem');
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
