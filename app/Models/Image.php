<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Image extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const COLLECTION_NAME = 'images';

    protected $fillable = ['name'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(self::COLLECTION_NAME)
        ;
    }

    /**
     * @return string[]
     */
    public static function getMimeTypeMap(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/avif',
            'image/bmp',
        ];
    }

    public function getUrl(): string
    {
        return $this->getFirstMediaUrl(self::COLLECTION_NAME);
    }
}
