<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\ResourcePermissions;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

abstract class ResourcePolicy
{
    /**
     * @var class-string<ResourcePermissions&BackedEnum>
     */
    protected static string $permissions;

    protected function allows(User $user, string $action): bool
    {
        $permission = static::$permissions::forAction($action);

        if ($permission === null) {
            return false;
        }

        return $user->can($permission);
    }

    public function viewAny(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function replicate(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, __FUNCTION__);
    }
}
