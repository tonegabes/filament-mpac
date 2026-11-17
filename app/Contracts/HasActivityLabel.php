<?php

declare(strict_types=1);

namespace App\Contracts;

interface HasActivityLabel
{
    public static function activityLabel(): string;
}
