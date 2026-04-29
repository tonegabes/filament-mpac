<?php

declare(strict_types=1);

use App\Contracts\HasFileUrl;
use App\Models\Document;

it('returns expected mime types for documents', function (): void {
    $mimes = Document::getMimeTypeMap();

    expect($mimes)->toBeArray()
        ->and($mimes)->toContain(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        )
        ->and($mimes)->toHaveCount(7);
});

it('has collection name constant', function (): void {
    expect(Document::COLLECTION_NAME)->toBe('documents');
});

it('can be used anywhere a file url record is expected', function (): void {
    expect(new Document)->toBeInstanceOf(HasFileUrl::class);
});
