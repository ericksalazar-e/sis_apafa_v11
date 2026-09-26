<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaenaAsistencia extends Model
{
    use HasFactory;

    protected $table = 'faena_asistencias';

    protected $fillable = [
        'faena_id',
        'padre_familia_id',
        'horas_trabajadas',
        'monto_compensado',
        'observaciones',
        'pago_id',
    ];

    protected function casts(): array
    {
        return [
            'horas_trabajadas' => 'decimal:1',
            'monto_compensado' => 'decimal:2',
        ];
    }

    public function faena(): BelongsTo
    {
        return $this->belongsTo(Faena::class, 'faena_id');
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class, 'padre_familia_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
