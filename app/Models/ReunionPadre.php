<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReunionPadre extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'reunion_padres';

    protected $fillable = [
        'empresa_id',
        'reunion_id',
        'padre_familia_id',
        'asistencia',
        'asistencia_salida',
        'detalles',
    ];

    protected function casts(): array
    {
        return [
            'asistencia' => 'datetime',
            'asistencia_salida' => 'datetime',
        ];
    }

    public function reunion(): BelongsTo
    {
        return $this->belongsTo(Reunion::class);
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class);
    }
}
