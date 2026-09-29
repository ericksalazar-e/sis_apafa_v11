<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AportePadre extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'aporte_padres';

    protected $fillable = [
        'empresa_id',
        'aporte_id',
        'padre_familia_id',
        'monto_aporte',
        'monto_aportado',
        'debe',
    ];

    protected function casts(): array
    {
        return [
            'monto_aporte' => 'decimal:2',
            'monto_aportado' => 'decimal:2',
            'debe' => 'decimal:2',
        ];
    }

    public function aporte(): BelongsTo
    {
        return $this->belongsTo(Aporte::class);
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class);
    }
}
