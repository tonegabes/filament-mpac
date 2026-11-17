<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Image;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    public function document(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        $media = $document->getFirstMedia(Document::fileCollection()->value);

        abort_if($media === null, 404);

        return $this->streamMedia($media);
    }

    public function image(Image $image): StreamedResponse
    {
        Gate::authorize('view', $image);

        $media = $image->getFirstMedia(Image::fileCollection()->value);

        abort_if($media === null, 404);

        return $this->streamMedia($media);
    }

    public function media(Media $media): StreamedResponse
    {
        Gate::authorize('view', $media);

        return $this->streamMedia($media);
    }

    private function streamMedia(Media $media): StreamedResponse
    {
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $media->file_name);
    }
}
