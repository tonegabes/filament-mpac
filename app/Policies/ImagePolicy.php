<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\ImagePermissions;
use App\Models\Image;
use App\Models\User;

class ImagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(ImagePermissions::ViewAny);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Image $image): bool
    {
        return $user->can(ImagePermissions::View);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(ImagePermissions::Create);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Image $image): bool
    {
        return $user->can(ImagePermissions::Update);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Image $image): bool
    {
        return $user->can(ImagePermissions::Delete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Image $image): bool
    {
        return $user->can(ImagePermissions::Restore);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Image $image): bool
    {
        return $user->can(ImagePermissions::ForceDelete);
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can(ImagePermissions::Delete);
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can(ImagePermissions::Restore);
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can(ImagePermissions::ForceDelete);
    }

    /**
     * Determine whether the user can replicate models.
     */
    public function replicate(User $user): bool
    {
        return $user->can(ImagePermissions::Replicate);
    }

    /**
     * Determine whether the user can reorder models.
     */
    public function reorder(User $user): bool
    {
        return $user->can(ImagePermissions::Reorder);
    }
}
