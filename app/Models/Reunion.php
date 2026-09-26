<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reunion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'reuniones';

    protected $fillable = [
        'empresa_id',
        'estado_reunion_id',
        'titulo',
        'descripcion',
        'multa_precio',
        'fecha',
        'hora',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'multa_precio' => 'decimal:2',
        ];
    }

    public function estadoReunion(): BelongsTo
    {
        return $this->belongsTo(EstadoReunion::class);
    }

    public function reunionesPadres(): HasMany
    {
        return $this->hasMany(ReunionPadre::class);
    }
}
