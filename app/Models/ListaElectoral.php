<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListaElectoral extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'listas_electorales';

    protected $fillable = [
        'comicio_id',
        'empresa_id',
        'numero_lista',
        'nombre_lista',
        'eslogan',
        'color',
        'logo_simbolo',
        'propuestas',
        'total_votos',
        'estado',
    ];

    public function comicio(): BelongsTo
    {
        return $this->belongsTo(ComicioElectoral::class, 'comicio_id');
    }

    public function candidatos(): HasMany
    {
        return $this->hasMany(CandidatoElectoral::class, 'lista_electoral_id');
    }

    public function votos(): HasMany
    {
        return $this->hasMany(VotoPadron::class, 'lista_electoral_id');
    }

    public function getPresidenteAttribute(): ?CandidatoElectoral
    {
        return $this->candidatos->where('cargo_postula', 'PRESIDENTE')->first();
    }
}
