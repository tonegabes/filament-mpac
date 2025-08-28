<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaCollections;
use App\Models\Scopes\MediaCollectionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy([MediaCollectionScope::class])]
class Image extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name'];

    public static function getCollection(): string
    {
        return MediaCollections::Images->value;
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::getCollection())
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
                'image/avif',
                'image/bmp',
            ]);
    }
}
