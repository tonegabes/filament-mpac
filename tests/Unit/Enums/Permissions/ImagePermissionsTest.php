<?php

declare(strict_types=1);

use App\Enums\Permissions\ImagePermissions;

it('has expected cases and string values', function (): void {
    expect(ImagePermissions::All->value)->toBe('images.*')
        ->and(ImagePermissions::ViewAny->value)->toBe('images.view.any')
        ->and(ImagePermissions::View->value)->toBe('images.view')
        ->and(ImagePermissions::Create->value)->toBe('images.create')
        ->and(ImagePermissions::Update->value)->toBe('images.update')
        ->and(ImagePermissions::Delete->value)->toBe('images.delete')
        ->and(ImagePermissions::Restore->value)->toBe('images.restore')
        ->and(ImagePermissions::ForceDelete->value)->toBe('images.force-delete')
        ->and(ImagePermissions::Replicate->value)->toBe('images.replicate')
        ->and(ImagePermissions::Reorder->value)->toBe('images.reorder');
});

it('has exactly ten cases', function (): void {
    expect(ImagePermissions::cases())->toHaveCount(10);
});
