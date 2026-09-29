<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaEstudiante extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'asistencia_estudiantes';

    protected $fillable = [
        'empresa_id',
        'estudiante_id',
        'fecha',
        'hora_ingreso',
        'hora_salida',
        'turno',
        'tipo_actividad',
        'taller_id',
        'estado',
        'justificado',
        'motivo_justificacion',
        'documento_sustento',
        'fecha_justificacion',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'justificado' => 'boolean',
            'fecha_justificacion' => 'datetime',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    public function getEstadoBadgeHtmlAttribute(): string
    {
        if ($this->estado === 'PUNTUAL') {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Puntual</span>';
        }

        if ($this->estado === 'TARDANZA') {
            if ($this->justificado) {
                return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="fa-solid fa-file-circle-check text-[10px]"></i>Tardanza Justificada</span>';
            }

            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Tardanza</span>';
        }

        if (str_contains($this->estado, 'JUSTIFICADA')) {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-shield-check text-[10px]"></i>'.e($this->estado).'</span>';
        }

        if (str_contains($this->estado, 'FALTA') || str_contains($this->estado, 'INASISTENCIA')) {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Inasistencia</span>';
        }

        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">'.e($this->estado).'</span>';
    }
}
