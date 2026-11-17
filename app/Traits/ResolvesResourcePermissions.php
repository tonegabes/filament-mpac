<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;

trait ResolvesResourcePermissions
{
    public static function forAction(string $action): ?BackedEnum
    {
        $caseName = match ($action) {
            'viewAny' => 'ViewAny',
            'view' => 'View',
            'create' => 'Create',
            'update' => 'Update',
            'delete', 'deleteAny' => 'Delete',
            'restore', 'restoreAny' => 'Restore',
            'forceDelete', 'forceDeleteAny' => 'ForceDelete',
            'replicate' => 'Replicate',
            'reorder' => 'Reorder',
            default => null,
        };

        if ($caseName === null) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($case->name === $caseName) {
                return $case;
            }
        }

        return null;
    }
}
