<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'pagos';

    protected $fillable = [
        'empresa_id',
        'padre_familia_id',
        'user_id',
        'pago_concepto',
        'monto_pago',
        'modalidad',
        'numero_cuota',
        'total_cuotas',
        'saldo_pendiente',
        'faena_id',
        'debe',
        'tipo_comprobante',
        'serie',
        'numero',
        'estado_facturacion',
        'hash_cpe',
        'mensaje_sunat',
        'pdf_url',
        'xml_url',
    ];

    public function faena(): BelongsTo
    {
        return $this->belongsTo(Faena::class, 'faena_id');
    }

    protected function casts(): array
    {
        return [
            'monto_pago' => 'decimal:2',
            'debe' => 'decimal:2',
        ];
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
