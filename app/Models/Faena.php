<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faena extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'faenas';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'descripcion',
        'fecha_faena',
        'lugar',
        'valor_compensacion',
        'cupo_maximo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_faena' => 'date',
            'valor_compensacion' => 'decimal:2',
        ];
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(FaenaAsistencia::class, 'faena_id');
    }
}
