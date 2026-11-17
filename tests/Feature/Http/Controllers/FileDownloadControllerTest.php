<?php

declare(strict_types=1);

use App\Enums\Permissions\DocumentPermissions;
use App\Enums\Permissions\ImagePermissions;
use App\Enums\Permissions\MediaPermissions;
use App\Models\Document;
use App\Models\Image;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    Storage::fake('documents');
    Storage::fake('images');

    Permission::firstOrCreate(['name' => DocumentPermissions::View->value]);
    Permission::firstOrCreate(['name' => ImagePermissions::View->value]);
    Permission::firstOrCreate(['name' => MediaPermissions::View->value]);
});

it('redirects unauthenticated users when downloading private files', function (): void {
    $document = Document::create(['name' => 'Secret Doc']);
    $document->addMedia(UploadedFile::fake()->createWithContent('secret.pdf', '%PDF-1.4 test content'))
        ->toMediaCollection(Document::fileCollection()->value);

    $image = Image::create(['name' => 'Secret Image']);
    $image->addMedia(UploadedFile::fake()->image('secret.png'))
        ->toMediaCollection(Image::fileCollection()->value);

    $media = $document->getFirstMedia(Document::fileCollection()->value);

    $this->get(route('documents.download', $document))->assertRedirect(route('login'));
    $this->get(route('images.download', $image))->assertRedirect(route('login'));
    $this->get(route('media.download', $media))->assertRedirect(route('login'));
});

it('forbids authenticated users without view permission', function (): void {
    $user = User::factory()->create();
    $document = Document::create(['name' => 'Secret Doc']);
    $document->addMedia(UploadedFile::fake()->createWithContent('secret.pdf', '%PDF-1.4 test content'))
        ->toMediaCollection(Document::fileCollection()->value);

    $this->actingAs($user)
        ->get(route('documents.download', $document))
        ->assertForbidden();
});

it('allows authorized users to download documents, images, and media', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo([
        DocumentPermissions::View->value,
        ImagePermissions::View->value,
        MediaPermissions::View->value,
    ]);

    $document = Document::create(['name' => 'Secret Doc']);
    $document->addMedia(UploadedFile::fake()->createWithContent('secret.pdf', '%PDF-1.4 test content'))
        ->toMediaCollection(Document::fileCollection()->value);

    $image = Image::create(['name' => 'Secret Image']);
    $image->addMedia(UploadedFile::fake()->image('secret.png'))
        ->toMediaCollection(Image::fileCollection()->value);

    $media = $document->getFirstMedia(Document::fileCollection()->value);

    $this->actingAs($user)
        ->get(route('documents.download', $document))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('images.download', $image))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('media.download', $media))
        ->assertSuccessful();
});
