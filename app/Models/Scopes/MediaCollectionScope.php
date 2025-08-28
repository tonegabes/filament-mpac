<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MediaCollectionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (method_exists($model, 'getCollection')) {
            $builder->whereHas(
                'media',
                fn (Builder $query) => $query->where('collection_name', $model->getCollection()),
            );
        }
    }
}
