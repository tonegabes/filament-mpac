<?php

declare(strict_types=1);

use App\Contracts\HasFileUrl;
use App\Models\Document;

it('has collection name constant', function (): void {
    expect(Document::fileCollection()->value)->toBe('documents');
});

it('can be used anywhere a file url record is expected', function (): void {
    $record = new Document;
    $acceptsFileUrl = fn (HasFileUrl $fileUrlRecord): HasFileUrl => $fileUrlRecord;

    expect($acceptsFileUrl($record))->toBe($record);
});
