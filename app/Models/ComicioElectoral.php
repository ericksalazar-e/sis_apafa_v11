<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComicioElectoral extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'comicios_electorales';

    protected $fillable = [
        'empresa_id',
        'año_electoral',
        'nombre_proceso',
        'fecha_corte_padron',
        'fecha_elecciones',
        'estado',
        'comite_presidente_nombre',
        'comite_presidente_dni',
        'comite_secretario_nombre',
        'comite_secretario_dni',
        'comite_vocal_nombre',
        'comite_vocal_dni',
    ];


    protected function casts(): array
    {
        return [
            'fecha_corte_padron' => 'date',
            'fecha_elecciones' => 'date',
        ];
    }

    public function votos(): HasMany
    {
        return $this->hasMany(VotoPadron::class, 'comicio_id');
    }

    public function listas(): HasMany
    {
        return $this->hasMany(ListaElectoral::class, 'comicio_id');
    }

    /**
     * Bienio para el cual se eligen los nuevos directivos (Mandato de 2 años - Ley 28628 Art. 43)
     */
    public function getBienioElectoAttribute(): string
    {
        $añoInicio = (int) ($this->año_electoral ?? date('Y')) + 1;
        $añoFin = $añoInicio + 1;
        return "Bienio {$añoInicio} - {$añoFin}";
    }

    /**
     * Bienio que concluye mandato (Último año de gestión)
     */
    public function getBienioSalienteAttribute(): string
    {
        $añoFin = (int) ($this->año_electoral ?? date('Y'));
        $añoInicio = $añoFin - 1;
        return "Bienio {$añoInicio} - {$añoFin}";
    }

    /**
     * Verifica si la fecha de elecciones cae en la ventana legal obligatoria
     * Comprendida entre el 1 de setiembre y el 30 de noviembre (Ley N° 28628 Art. 48)
     */
    public function getEsVentanaLegalValidaAttribute(): bool
    {
        if (!$this->fecha_elecciones) {
            return false;
        }

        $mes = (int) $this->fecha_elecciones->format('m');
        return ($mes >= 9 && $mes <= 11);
    }

    /**
     * Descripción normativa del periodo legal de sufragio
     */
    public function getVentanaLegalDescripcionAttribute(): string
    {
        return "Periodo obligatorio de elecciones: 01 de setiembre al 30 de noviembre del último año de gestión (Ley N.° 28628 Art. 48 / D.S. N.° 007-2026-MINEDU).";
    }

    /**
     * Validador estático de fecha electoral según la ventana legal
     */
    public static function validarFechaEnVentanaLegal($fecha): bool
    {
        if (!$fecha) return false;
        $timestamp = is_numeric($fecha) ? $fecha : strtotime($fecha);
        $mes = (int) date('n', $timestamp);
        return ($mes >= 9 && $mes <= 11);
    }

    /**
     * Obtener la lista ganadora proclamada o con mayor votación
     */
    public function getListaGanadoraAttribute()
    {
        if (!$this->relationLoaded('listas')) {
            $this->load(['listas.candidatos.padreFamilia.estudiantes']);
        }

        return $this->listas->where('estado', 'GANADORA')->first()
            ?? $this->listas->sortByDesc('total_votos')->first()
            ?? $this->listas->first();
    }
}


