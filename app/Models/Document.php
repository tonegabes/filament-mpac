<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasFileUrl;
use App\Enums\FileCollection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasFileUrl, HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name'];

    public const COLLECTION_NAME = FileCollection::Documents->value;

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(FileCollection::Documents->disk())
        ;
    }

    /**
     * @return string[]
     */
    public static function getMimeTypeMap(): array
    {
        return FileCollection::Documents->acceptedMimeTypes();
    }

    public function getUrl(): string
    {
        return $this->getFileUrl();
    }

    public function getFileUrl(): string
    {
        return $this->getFirstMediaUrl(self::COLLECTION_NAME);
    }

    public function getFilename(): string
    {
        return $this->getFirstMedia(self::COLLECTION_NAME)->file_name ?? '';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
        ;
    }
}
