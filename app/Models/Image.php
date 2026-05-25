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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Image extends Model implements HasFileUrl, HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    public const COLLECTION_NAME = FileCollection::Images->value;

    protected $fillable = ['name'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::COLLECTION_NAME)
            ->acceptsMimeTypes(self::getMimeTypeMap())
            ->useDisk(FileCollection::Images->disk())
        ;
    }

    /**
     * @return string[]
     */
    public static function getMimeTypeMap(): array
    {
        return FileCollection::Images->acceptedMimeTypes();
    }

    public function getFileUrl(): string
    {
        return $this->getFirstMediaUrl(self::COLLECTION_NAME);
    }

    public function getFilename(): string
    {
        return $this->getFirstMedia(self::COLLECTION_NAME)->file_name ?? '';
    }

    public static function getMediaByName(string $name): ?Media
    {
        return Media::where([
            ['file_name', $name],
            ['collection_name', self::COLLECTION_NAME],
        ])->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
        ;
    }
}
