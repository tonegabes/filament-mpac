<?php

declare(strict_types=1);

namespace App\Contracts;

interface HasFileUrl
{
    public function getFileUrl(): string;
}
