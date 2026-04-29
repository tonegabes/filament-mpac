<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name'];

    public const COLLECTION_NAME = 'documents';

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(self::COLLECTION_NAME)
        ;

        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232);
    }

    /**
     * @return string[]
     */
    public static function getMimeTypeMap(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
    }

    public function getUrl(): string
    {
        return $this->getFirstMediaUrl(self::COLLECTION_NAME);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
        ;
    }
}
