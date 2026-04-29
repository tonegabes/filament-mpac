<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\FileCollection;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileLibrary
{
    public static function collectionLabel(?string $collectionName): string
    {
        $collection = self::collectionFrom($collectionName);

        return $collection?->label() ?? ($collectionName ?: 'Arquivos');
    }

    public static function typeLabel(?string $mimeType): string
    {
        if ($mimeType === null || $mimeType === '') {
            return 'Arquivo';
        }

        if (str_starts_with($mimeType, 'image/')) {
            return 'Imagem';
        }

        return match ($mimeType) {
            'application/pdf' => 'PDF',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Documento',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Planilha',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'Apresentação',
            default                                                                     => 'Arquivo',
        };
    }

    public static function formatSize(?int $bytes): string
    {
        if ($bytes === null) {
            return '-';
        }

        return Number::fileSize($bytes);
    }

    public static function url(Media $media): string
    {
        return $media->getUrl();
    }

    public static function isImage(?string $mimeType): bool
    {
        return is_string($mimeType) && str_starts_with($mimeType, 'image/');
    }

    private static function collectionFrom(?string $collectionName): ?FileCollection
    {
        if ($collectionName === null) {
            return null;
        }

        return FileCollection::tryFrom($collectionName);
    }
}
