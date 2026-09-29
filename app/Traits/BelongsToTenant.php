<?php

namespace App\Traits;

use App\Models\Empresa;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait to apply the tenant global scope and auto-assign empresa_id.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->empresa_id)) {
                $tenantId = session('empresa_id');
                if (! $tenantId && auth()->check() && ! empty(auth()->user()->empresa_id)) {
                    $tenantId = auth()->user()->empresa_id;
                }
                $model->empresa_id = $tenantId ?? 1;
            }
        });
    }

    /**
     * Get the empresa (tenant) associated with the model.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
