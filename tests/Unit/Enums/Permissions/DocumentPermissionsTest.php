<?php

declare(strict_types=1);

use App\Enums\Permissions\DocumentPermissions;

it('has expected cases and string values', function (): void {
    expect(DocumentPermissions::All->value)->toBe('documents.*')
        ->and(DocumentPermissions::ViewAny->value)->toBe('documents.view.any')
        ->and(DocumentPermissions::View->value)->toBe('documents.view')
        ->and(DocumentPermissions::Create->value)->toBe('documents.create')
        ->and(DocumentPermissions::Update->value)->toBe('documents.update')
        ->and(DocumentPermissions::Delete->value)->toBe('documents.delete')
        ->and(DocumentPermissions::Restore->value)->toBe('documents.restore')
        ->and(DocumentPermissions::ForceDelete->value)->toBe('documents.force-delete')
        ->and(DocumentPermissions::Replicate->value)->toBe('documents.replicate')
        ->and(DocumentPermissions::Reorder->value)->toBe('documents.reorder');
});

it('has exactly ten cases', function (): void {
    expect(DocumentPermissions::cases())->toHaveCount(10);
});
