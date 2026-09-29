<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradoSeccionTurno extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'grados_secciones_turnos';

    protected $fillable = [
        'empresa_id',
        'nivel',
        'grado',
        'seccion',
        'turno',
        'tutor',
        'aula',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Sincroniza y actualiza el campo turno de todos los estudiantes que pertenezcan a este grado y sección.
     */
    public function sincronizarAlumnosConTurno(): int
    {
        return Estudiante::withoutGlobalScopes()
            ->where('empresa_id', $this->empresa_id)
            ->whereRaw('LOWER(nivel) = ?', [strtolower(trim($this->nivel))])
            ->whereRaw('LOWER(grado) = ?', [strtolower(trim($this->grado))])
            ->whereRaw('LOWER(seccion) = ?', [strtolower(trim($this->seccion))])
            ->update(['turno' => $this->turno]);
    }

    /**
     * Obtiene el turno oficial asignado a un grado y sección, o el valor por defecto ('Mañana').
     */
    public static function obtenerTurnoPredeterminado(int $empresaId, ?string $nivel, ?string $grado, ?string $seccion): string
    {
        if (empty($nivel) || empty($grado) || empty($seccion)) {
            return 'Mañana';
        }

        $config = static::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereRaw('LOWER(nivel) = ?', [strtolower(trim($nivel))])
            ->whereRaw('LOWER(grado) = ?', [strtolower(trim($grado))])
            ->whereRaw('LOWER(seccion) = ?', [strtolower(trim($seccion))])
            ->first();

        return $config ? $config->turno : 'Mañana';
    }

    /**
     * Conteo de estudiantes registrados actualmente en este grado y sección.
     */
    public function totalEstudiantes(): int
    {
        return Estudiante::withoutGlobalScopes()
            ->where('empresa_id', $this->empresa_id)
            ->whereRaw('LOWER(nivel) = ?', [strtolower(trim($this->nivel))])
            ->whereRaw('LOWER(grado) = ?', [strtolower(trim($this->grado))])
            ->whereRaw('LOWER(seccion) = ?', [strtolower(trim($this->seccion))])
            ->count();
    }
}
