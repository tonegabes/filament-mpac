<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasActiveScope
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeIsActive(Builder $query): Builder
    {
        $query->where('is_active', true);

        if (DB::connection()->getDriverName() === 'mysql') {
            $query->useIndex('idx_is_active');
        }

        return $query;
    }

    /**
     * Activate the model.
     */
    public function activate(): bool
    {
        /* @var Model $this */
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the model.
     */
    public function deactivate(): bool
    {
        /* @var Model $this */
        return $this->update(['is_active' => false]);
    }

    /**
     * Check if the model is active.
     */
    public function isActive(): bool
    {
        /* @var Model $this */
        return (bool) $this->is_active;
    }

    /**
     * Check if the model is inactive.
     */
    public function isInactive(): bool
    {
        /* @var Model $this */
        return ! $this->isActive();
    }

    /**
     * Toggle the active state of the model.
     */
    public function toggleActive(): bool
    {
        /* @var Model $this */
        return $this->update(['is_active' => ! $this->isActive()]);
    }

    /**
     * Count the number of active models.
     *
     * @param  Builder<Model>  $query
     */
    public function scopeActiveCount(Builder $query): int
    {
        return $query->where('is_active', true)->count();
    }
}
