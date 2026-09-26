<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotoPadron extends Model
{
    use HasFactory;

    protected $table = 'votos_padron';

    protected $fillable = [
        'comicio_id',
        'padre_familia_id',
        'ha_votado',
        'fecha_hora_voto',
        'mesa_numero',
        'lista_electoral_id',
        'tipo_voto',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'ha_votado' => 'boolean',
            'fecha_hora_voto' => 'datetime',
        ];
    }

    public function comicio(): BelongsTo
    {
        return $this->belongsTo(ComicioElectoral::class, 'comicio_id');
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class, 'padre_familia_id');
    }

    public function listaElectoral(): BelongsTo
    {
        return $this->belongsTo(ListaElectoral::class, 'lista_electoral_id');
    }
}
