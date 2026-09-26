<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoQuiosco extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'pagos_quioscos';

    protected $fillable = [
        'concesion_id',
        'empresa_id',
        'mes_correspondiente',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'comprobante_numero',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function concesion(): BelongsTo
    {
        return $this->belongsTo(ConcesionQuiosco::class, 'concesion_id');
    }
}
