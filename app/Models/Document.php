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

    public function registerMediaCollections(): void
    {
        $collection = self::fileCollection();

        $this
            ->addMediaCollection($collection->value)
            ->acceptsMimeTypes($collection->acceptedMimeTypes())
            ->useDisk(FileCollection::Documents->disk());
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
        return $this->getFirstMediaUrl(self::fileCollection()->value);
    }

    public function getFilename(): string
    {
        return $this->getFirstMedia(self::fileCollection()->value)->file_name ?? '';
    }

    public static function fileCollection(): FileCollection
    {
        return FileCollection::Documents;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty();
    }
}
