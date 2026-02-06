<?php

declare(strict_types=1);

use App\Models\Image;

it('returns expected mime types for images', function (): void {
    $mimes = Image::getMimeTypeMap();

    expect($mimes)->toBeArray()
        ->and($mimes)->toContain(
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/avif',
            'image/bmp'
        )
        ->and($mimes)->toHaveCount(8);
});

it('has collection name constant', function (): void {
    expect(Image::COLLECTION_NAME)->toBe('images');
});
