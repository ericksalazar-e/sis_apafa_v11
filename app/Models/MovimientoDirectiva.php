<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoDirectiva extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'movimientos_directiva';

    protected $fillable = [
        'empresa_id',
        'cargo_nombre',
        'directivo_saliente_nombre',
        'directivo_saliente_dni',
        'causal_vacancia',
        'fecha_vacancia',
        'directivo_entrante_nombre',
        'directivo_entrante_dni',
        'directivo_entrante_parentesco',
        'numero_acta_asamblea',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vacancia' => 'date',
        ];
    }

    public static function causalesDisponibles(): array
    {
        return [
            'FUNCION_CUMPLIDA' => [
                'label' => 'Función Cumplida (Conclusión de Mandato Bienal - 2 Años)',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icono' => 'fa-award',
            ],
            'NO_ASUME_CARGO' => [
                'label' => 'No Desea Asumir Funciones / Desistimiento Voluntario',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icono' => 'fa-user-slash',
            ],
            'RENUNCIA_VOLUNTARIA' => [
                'label' => 'Renuncia formal voluntaria aceptada',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'icono' => 'fa-file-signature',
            ],
            'INASISTENCIA_3_ASAMBLEAS' => [
                'label' => 'Inasistencia injustificada a 3 Asambleas Consecutivas',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'icono' => 'fa-calendar-xmark',
            ],
            'EGRESO_DEL_HIJO' => [
                'label' => 'Egreso / Retiro del último hijo de la I.E.',
                'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icono' => 'fa-graduation-cap',
            ],
            'FALLECIMIENTO_O_INCAPACIDAD' => [
                'label' => 'Fallecimiento o incapacidad sobrevenida',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-300',
                'icono' => 'fa-heart-pulse',
            ],
            'DESTITUCION_POR_ASAMBLEA' => [
                'label' => 'Destitución acordada por Asamblea General',
                'badge' => 'bg-red-50 text-red-700 border-red-200',
                'icono' => 'fa-gavel',
            ],
        ];
    }

    public function getCausalLabelAttribute(): string
    {
        $causales = self::causalesDisponibles();
        return $causales[$this->causal_vacancia]['label'] ?? $this->causal_vacancia;
    }

    public function getCausalBadgeAttribute(): string
    {
        $causales = self::causalesDisponibles();
        return $causales[$this->causal_vacancia]['badge'] ?? 'bg-slate-100 text-slate-600 border-slate-200';
    }

    public function getCausalIconoAttribute(): string
    {
        $causales = self::causalesDisponibles();
        return $causales[$this->causal_vacancia]['icono'] ?? 'fa-circle-info';
    }
}

