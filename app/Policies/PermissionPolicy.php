<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\PermissionPermissions;
use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionPermissions::ViewAny);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->can(PermissionPermissions::View);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionPermissions::Create);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->can(PermissionPermissions::Update);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->can(PermissionPermissions::Delete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->can(PermissionPermissions::Restore);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->can(PermissionPermissions::ForceDelete);
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionPermissions::Delete);
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can(PermissionPermissions::Restore);
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can(PermissionPermissions::ForceDelete);
    }

    /**
     * Determine whether the user can replicate models.
     */
    public function replicate(User $user): bool
    {
        return $user->can(PermissionPermissions::Replicate);
    }

    /**
     * Determine whether the user can reorder models.
     */
    public function reorder(User $user): bool
    {
        return $user->can(PermissionPermissions::Reorder);
    }
}
