<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taller extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'talleres';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'nivel',
        'grado',
        'seccion',
        'turno',
        'hora_inicio',
        'hora_limite_puntual',
        'hora_fin',
        'dias_semana',
        'docente_responsable',
        'lugar',
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

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'taller_id');
    }

    /**
     * Retorna los días de la semana como arreglo limpio.
     */
    public function getDiasArrayAttribute(): array
    {
        if (empty($this->dias_semana)) {
            return [];
        }

        return array_map('trim', explode(',', $this->dias_semana));
    }

    /**
     * Verifica si el taller aplica a un estudiante específico según su nivel, grado y sección.
     */
    public function aplicaAEstudiante(Estudiante $estudiante): bool
    {
        // Verificar nivel
        if (! empty($this->nivel) && strtolower($this->nivel) !== 'todos') {
            if (strtolower(trim($this->nivel)) !== strtolower(trim($estudiante->nivel ?? ''))) {
                return false;
            }
        }

        // Verificar grado
        if (! empty($this->grado) && strtolower($this->grado) !== 'todos') {
            if (strtolower(trim($this->grado)) !== strtolower(trim($estudiante->grado ?? ''))) {
                return false;
            }
        }

        // Verificar sección
        if (! empty($this->seccion) && strtolower($this->seccion) !== 'todas' && strtolower($this->seccion) !== 'todos') {
            if (strtolower(trim($this->seccion)) !== strtolower(trim($estudiante->seccion ?? ''))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determina si hoy (o en el día dado) se dicta el taller.
     */
    public function esDiaProgramado(?string $diaNombre = null): bool
    {
        if (empty($diaNombre)) {
            $diasEspanol = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
            ];
            $diaNombre = $diasEspanol[(int) date('w')] ?? 'Lunes';
        }

        $diasArr = array_map(fn ($d) => mb_strtolower(trim($d), 'UTF-8'), $this->dias_array);
        $target = mb_strtolower(trim($diaNombre), 'UTF-8');

        // Normalizar acentos
        $targetNorm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $target);

        foreach ($diasArr as $d) {
            $dNorm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $d);
            if ($dNorm === $targetNorm || str_contains($dNorm, $targetNorm) || str_contains($targetNorm, $dNorm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si la hora actual está dentro de la ventana de acceso al taller (desde 30 minutos antes hasta la hora de salida).
     */
    public function estaEnHorario(?string $hora = null): bool
    {
        $hora = $hora ?? date('H:i:s');
        $tsActual = strtotime($hora);

        $tsInicioVentana = strtotime($this->hora_inicio) - (30 * 60); // 30 minutos antes
        $tsFinVentana = strtotime($this->hora_fin) + (30 * 60); // hasta 30 minutos después

        return $tsActual >= $tsInicioVentana && $tsActual <= $tsFinVentana;
    }
}
