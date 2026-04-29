<?php

declare(strict_types=1);

use App\Enums\FileCollection;
use App\Support\FileLibrary;

it('returns library collection options', function (): void {
    expect(FileCollection::options())->toMatchArray([
        'images'             => 'Imagens',
        'documents'          => 'Documentos',
        'system_logos'       => 'Logos do sistema',
        'system_backgrounds' => 'Fundos do sistema',
    ]);
});

it('formats media metadata for display', function (): void {
    expect(FileLibrary::collectionLabel('images'))->toBe('Imagens')
        ->and(FileLibrary::collectionLabel('unknown'))->toBe('unknown')
        ->and(FileLibrary::typeLabel('image/png'))->toBe('Imagem')
        ->and(FileLibrary::typeLabel('application/pdf'))->toBe('PDF')
        ->and(FileLibrary::isImage('image/webp'))->toBeTrue()
        ->and(FileLibrary::isImage('application/pdf'))->toBeFalse();
});
