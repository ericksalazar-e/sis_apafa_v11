<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'estudiantes';

    protected $fillable = [
        'empresa_id',
        'padre_familia_id',
        'parentesco',
        'dni',
        'nombres',
        'ape_pat',
        'ape_mat',
        'grado',
        'seccion',
        'nivel',
        'turno',
        'estado_matricula',
        'correlativo',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($estudiante) {
            if (empty($estudiante->correlativo)) {
                $count = static::withoutGlobalScopes()->count() + 1;
                $year = date('Y');
                $estudiante->correlativo = sprintf('N° %05d - %s', $count, $year);
            }
        });
    }

    public function padreFamilia(): BelongsTo
    {
        return $this->belongsTo(PadreFamilia::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->ape_pat} {$this->ape_mat}");
    }

    public function getApellidosAttribute(): string
    {
        return trim("{$this->ape_pat} {$this->ape_mat}");
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class);
    }

    /**
     * Retorna los talleres extracurriculares que corresponden al nivel, grado y sección del estudiante.
     */
    public function talleres()
    {
        $nivel = strtolower(trim($this->nivel ?? ''));
        $grado = strtolower(trim($this->grado ?? ''));
        $seccion = strtolower(trim($this->seccion ?? ''));

        return Taller::where('empresa_id', $this->empresa_id)
            ->where('activo', true)
            ->where(function ($q) use ($nivel) {
                $q->where('nivel', 'Todos')
                    ->orWhereRaw('LOWER(nivel) = ?', [$nivel]);
            })
            ->where(function ($q) use ($grado) {
                $q->where('grado', 'Todos')
                    ->orWhereRaw('LOWER(grado) = ?', [$grado]);
            })
            ->where(function ($q) use ($seccion) {
                $q->where('seccion', 'Todas')
                    ->orWhere('seccion', 'Todos')
                    ->orWhereRaw('LOWER(seccion) = ?', [$seccion]);
            })
            ->get();
    }
}
