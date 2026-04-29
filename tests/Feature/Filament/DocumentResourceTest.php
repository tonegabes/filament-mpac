<?php

declare(strict_types=1);

use App\Enums\Roles;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Pages\ViewDocument;
use App\Models\Document;
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

    Storage::fake(Document::COLLECTION_NAME);

    $user = User::factory()->create();
    $user->assignRole(Roles::Developer->value);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function createDocumentRecord(string $name, string $fileName): Document
{
    $document = Document::create(['name' => $name]);

    $document
        ->addMedia(UploadedFile::fake()->createWithContent($fileName, "%PDF-1.4\n% Test PDF\n"))
        ->toMediaCollection(Document::COLLECTION_NAME);

    return $document->refresh();
}

it('can render list documents page and see records', function (): void {
    $document = createDocumentRecord('Policy document', 'policy.pdf');

    Livewire::test(ListDocuments::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$document]);
});

it('can search documents by name', function (): void {
    $visible = createDocumentRecord('Annual report', 'annual-report.pdf');
    $hidden = createDocumentRecord('Budget sheet', 'budget.pdf');

    Livewire::test(ListDocuments::class)
        ->searchTable('Annual')
        ->assertCanSeeTableRecords([$visible])
        ->assertCanNotSeeTableRecords([$hidden]);
});

it('can render the document view page', function (): void {
    $document = createDocumentRecord('Viewable document', 'viewable.pdf');

    Livewire::test(ViewDocument::class, ['record' => $document->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => 'Viewable document',
        ]);
});

it('can delete a document from the table', function (): void {
    $document = createDocumentRecord('Disposable document', 'disposable.pdf');

    Livewire::test(ListDocuments::class)
        ->callAction(TestAction::make(DeleteAction::class)->table($document))
        ->assertNotified()
        ->assertCanNotSeeTableRecords([$document]);

    $this->assertDatabaseMissing(Document::class, [
        'id' => $document->id,
    ]);
});
