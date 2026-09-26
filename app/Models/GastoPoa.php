<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoPoa extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'gastos_poa';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'categoria',
        'titulo',
        'descripcion',
        'monto',
        'nro_comprobante',
        'proveedor',
        'fecha',
        'foto_comprobante',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Devuelve la información visual y legal de la categoría del POA.
     */
    public function getCategoriaInfoAttribute(): array
    {
        return match ($this->categoria) {
            'TECNOLOGIA' => [
                'nombre' => 'Tecnología Educativa y Sistemas',
                'icono' => 'fa-solid fa-laptop-code',
                'bg' => 'bg-indigo-50',
                'text' => 'text-indigo-700',
                'border' => 'border-indigo-200',
            ],
            'DEPORTE_LUDICO' => [
                'nombre' => 'Deporte y Psicomotricidad',
                'icono' => 'fa-solid fa-futbol',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-700',
                'border' => 'border-emerald-200',
            ],
            'CULTURAL_TALLERES' => [
                'nombre' => 'Actividades Culturales y Escuela de Padres',
                'icono' => 'fa-solid fa-masks-theater',
                'bg' => 'bg-purple-50',
                'text' => 'text-purple-700',
                'border' => 'border-purple-200',
            ],
            'EMERGENCIAS_BIENESTAR' => [
                'nombre' => 'Botiquín y Emergencias Escolares',
                'icono' => 'fa-solid fa-kit-medical',
                'bg' => 'bg-rose-50',
                'text' => 'text-rose-700',
                'border' => 'border-rose-200',
            ],
            default => [
                'nombre' => 'Gestión Operativa APAFA',
                'icono' => 'fa-solid fa-briefcase',
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-700',
                'border' => 'border-amber-200',
            ],
        };
    }
}
