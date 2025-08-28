<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaCollections: string
{
    case Images = 'images';
    case Documents = 'documents';
    case Videos = 'videos';
    case Audios = 'audios';
    case Others = 'others';
}
