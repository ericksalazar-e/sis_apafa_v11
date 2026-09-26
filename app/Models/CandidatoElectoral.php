<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatoElectoral extends Model
{
    use HasFactory;

    protected $table = 'candidatos_electorales';

    protected $fillable = [
        'lista_electoral_id',
        'padre_familia_id',
        'cargo_postula',
        'cumple_requisitos',
        'al_dia_pagos',
        'declara_sin_antecedentes',
        'declara_no_labora_ie',
        'declara_no_removido',
        'grado_estudiante_valido',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cumple_requisitos' => 'boolean',
            'al_dia_pagos' => 'boolean',
            'declara_sin_antecedentes' => 'boolean',
            'declara_no_labora_ie' => 'boolean',
            'declara_no_removido' => 'boolean',
            'grado_estudiante_valido' => 'boolean',
        ];
    }

    public function lista(): BelongsTo
    {
        return $this->belongsTo(ListaElectoral::class, 'lista_electoral_id');
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class, 'padre_familia_id');
    }

    public function getCargoTextoAttribute(): string
    {
        $cargos = [
            'PRESIDENTE' => 'Presidente(a) APAFA',
            'VICEPRESIDENTE' => 'Vicepresidente(a) APAFA',
            'SECRETARIO' => 'Secretario(a) APAFA',
            'TESORERO' => 'Tesorero(a) APAFA',
            'VOCAL_1' => 'Primer(a) Vocal',
            'VOCAL_2' => 'Segundo(a) Vocal',
            'VOCAL_3' => 'Tercer(a) Vocal',
            'CONSEJO_VIGILANCIA_PRESIDENTE' => 'Presidente(a) Consejo de Vigilancia',
            'CONSEJO_VIGILANCIA_SECRETARIO' => 'Secretario(a) Consejo de Vigilancia',
            'CONSEJO_VIGILANCIA_VOCAL' => 'Vocal Consejo de Vigilancia',
        ];

        return $cargos[$this->cargo_postula] ?? $this->cargo_postula;
    }
}
