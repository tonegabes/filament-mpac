<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasIsActiveScope
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    #[Scope]
    protected function isActive(Builder $query): Builder
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
     * Toggle the active state of the model.
     */
    public function toggleActive(): bool
    {
        /* @var Model $this */
        return $this->update(['is_active' => ! $this->is_active]);
    }

    /**
     * Count the number of active models.
     *
     * @param  Builder<Model>  $query
     */
    public function countActive(Builder $query): int
    {
        return $query->where('is_active', true)->count();
    }
}
