<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends ResourcePolicy
{
    protected static string $permissions = UserPermissions::class;

    public function update(User $user, Model $model): bool
    {
        if (! $model instanceof User) {
            return false;
        }

        return parent::update($user, $model) && $user->canManageUser($model);
    }

    public function delete(User $user, Model $model): bool
    {
        if (! $model instanceof User) {
            return false;
        }

        return parent::delete($user, $model)
            && ! $user->is($model)
            && $user->canManageUser($model)
            && ! $model->isLastDeveloper();
    }

    public function forceDelete(User $user, Model $model): bool
    {
        if (! $model instanceof User) {
            return false;
        }

        return parent::forceDelete($user, $model)
            && ! $user->is($model)
            && $user->canManageUser($model)
            && ! $model->isLastDeveloper();
    }
}
