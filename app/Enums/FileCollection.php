<?php

declare(strict_types=1);

namespace App\Enums;

enum FileCollection: string
{
    case Images = 'images';
    case Documents = 'documents';
    case SystemLogos = 'system_logos';
    case SystemBackgrounds = 'system_backgrounds';

    public function label(): string
    {
        return match ($this) {
            self::Images            => 'Imagens',
            self::Documents         => 'Documentos',
            self::SystemLogos       => 'Logos do sistema',
            self::SystemBackgrounds => 'Fundos do sistema',
        };
    }

    public function disk(): string
    {
        return match ($this) {
            self::Images                               => 'images',
            self::Documents                            => 'documents',
            self::SystemLogos, self::SystemBackgrounds => 'public',
        };
    }

    public function directory(): ?string
    {
        return match ($this) {
            self::Images, self::Documents => null,
            self::SystemLogos             => 'system/logos',
            self::SystemBackgrounds       => 'system/backgrounds',
        };
    }

    /**
     * @return list<string>
     */
    public function acceptedMimeTypes(): array
    {
        return match ($this) {
            self::Images, self::SystemLogos, self::SystemBackgrounds => [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
                'image/avif',
                'image/bmp',
            ],
            self::Documents => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $collection): array => [$collection->value => $collection->label()])
            ->all();
    }
}
