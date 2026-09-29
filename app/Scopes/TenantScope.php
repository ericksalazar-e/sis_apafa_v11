<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = session('empresa_id');
        if (! $tenantId && auth()->check() && auth()->user()->empresa_id) {
            $tenantId = auth()->user()->empresa_id;
        }

        if ($tenantId) {
            $builder->where($model->getTable().'.empresa_id', $tenantId);
        }
    }
}
