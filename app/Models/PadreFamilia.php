<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PadreFamilia extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'padres_familia';

    protected $fillable = [
        'empresa_id',
        'cargo_id',
        'tipo_consanguinidad_id',
        'parentesco_alternativo',
        'padre_dni',
        'padre_nombres',
        'padre_ape_pat',
        'padre_ape_mat',
        'conyuge_nombres',
        'conyuge_dni',
        'conyuge_celular',
        'celular',
        'direccion',
        'correlativo',
        'fecha_creacion',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($padre) {
            if (empty($padre->correlativo)) {
                $count = static::withoutGlobalScopes()->count() + 1;
                $padre->correlativo = sprintf('N° %05d - %s', $count, date('Y'));
            }
        });
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function tipoConsanguinidad(): BelongsTo
    {
        return $this->belongsTo(TipoConsanguinidad::class, 'tipo_consanguinidad_id');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }

    public function aportes(): HasMany
    {
        return $this->hasMany(AportePadre::class);
    }

    public function reuniones(): HasMany
    {
        return $this->hasMany(ReunionPadre::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function asistenciasFaenas(): HasMany
    {
        return $this->hasMany(FaenaAsistencia::class, 'padre_familia_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->padre_nombres} {$this->padre_ape_pat} {$this->padre_ape_mat}");
    }

    public function getParentescoTextoAttribute(): string
    {
        if ($this->parentesco_alternativo) {
            return $this->parentesco_alternativo;
        }
        return $this->tipoConsanguinidad->nombre ?? 'PADRE';
    }

    public function getRepresentacionFamiliarAttribute(): string
    {
        if ($this->conyuge_nombres) {
            return "{$this->nombre_completo} & {$this->conyuge_nombres}";
        }
        return $this->nombre_completo;
    }

    public function getDniAttribute(): ?string
    {
        return $this->padre_dni;
    }

    public function getNombresAttribute(): ?string
    {
        return $this->padre_nombres;
    }

    public function getApellidosAttribute(): ?string
    {
        return trim("{$this->padre_ape_pat} {$this->padre_ape_mat}");
    }

    /**
     * Verificación de requisitos legales para ser elegido en APAFA (Ley N° 28628 Art. 45)
     */
    public function verificarRequisitosApafa(): array
    {
        $errores = [];
        $cumple = true;
        $estudiantes = $this->estudiantes;

        // 1. Condición de asociado con estudiante matriculado
        if ($estudiantes->isEmpty()) {
            $cumple = false;
            $errores[] = 'No registra ningún hijo o pupilo matriculado en la Institución Educativa.';
        }

        // 2. Grado del estudiante: no debe estar cursando el último grado que brinda la I.E. (salvo Inicial)
        $tieneHijoEnGradoContinuo = false;
        if ($estudiantes->isNotEmpty()) {
            foreach ($estudiantes as $est) {
                $nivel = strtoupper(trim($est->nivel ?? ''));
                $gradoStr = trim($est->grado ?? '');
                preg_match('/\d+/', $gradoStr, $matches);
                $gradoNum = isset($matches[0]) ? (int) $matches[0] : 0;

                // Excepción legal explícita: Educación Inicial
                if (str_contains($nivel, 'INICIAL')) {
                    $tieneHijoEnGradoContinuo = true;
                    break;
                }

                // Secundaria (el último grado es 5°)
                if (str_contains($nivel, 'SECUNDARIA')) {
                    if ($gradoNum < 5 && $gradoNum > 0) {
                        $tieneHijoEnGradoContinuo = true;
                        break;
                    }
                }
                // Primaria (el último grado es 6°)
                elseif (str_contains($nivel, 'PRIMARIA')) {
                    if ($gradoNum < 6 && $gradoNum > 0) {
                        $tieneHijoEnGradoContinuo = true;
                        break;
                    }
                } else {
                    if ($gradoNum < 5 && $gradoNum > 0) {
                        $tieneHijoEnGradoContinuo = true;
                        break;
                    }
                }
            }

            if (!$tieneHijoEnGradoContinuo) {
                $cumple = false;
                $errores[] = 'Incompatibilidad Legal (Art. 45 inc. e): Su hijo o todos sus pupilos cursan el último grado de la I.E. y egresarán este año.';
            }
        }

        // 3. Estar al día en cuotas ordinarias o compensación por faena
        $tienePagos = $this->pagos()->where('estado_facturacion', '!=', 'ANULADO')->exists();
        $tieneCompensacion = $this->asistenciasFaenas()->exists();
        $estaAlDia = $tienePagos || $tieneCompensacion;

        return [
            'cumple' => $cumple,
            'errores' => $errores,
            'esta_al_dia' => $estaAlDia,
            'tiene_grado_valido' => $tieneHijoEnGradoContinuo || $estudiantes->isEmpty(),
            'estudiantes_count' => $estudiantes->count(),
        ];
    }
}
