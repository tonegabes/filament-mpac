<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 *
 * @property bool $is_active
 */
trait HasIsActiveScope
{
    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        $query->where('is_active', true);

        if (DB::connection()->getDriverName() === 'mysql') {
            $query->useIndex('idx_is_active');
        }

        return $query;
    }

    /**
     * Determine if the model is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Determine if the model is inactive.
     */
    public function isInactive(): bool
    {
        return ! $this->isActive();
    }

    /**
     * Activate the model.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the model.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Toggle the active state of the model.
     */
    public function toggleActive(): bool
    {
        return $this->update(['is_active' => ! $this->is_active]);
    }

    /**
     * Count the number of active models.
     *
     * @param  Builder<covariant Model>  $query
     */
    #[Scope]
    protected function activeCount(Builder $query): int
    {
        return $query->where('is_active', true)->count();
    }
}
