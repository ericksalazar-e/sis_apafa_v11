<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'cod_modular',
        'ruc',
        'ubicacion',
        'telefono',
        'correo',
        'periodo_apafa',
        'lema',
        'nivel_educativo',
        'director_nombre',
        'foto_institucion',
        'foto_ugel',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->foto_institucion)) {
            return null;
        }
        if (str_starts_with($this->foto_institucion, 'http')) {
            return $this->foto_institucion;
        }
        return asset($this->foto_institucion);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function padres(): HasMany
    {
        return $this->hasMany(PadreFamilia::class);
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }

    public function aportes(): HasMany
    {
        return $this->hasMany(Aporte::class);
    }

    public function reuniones(): HasMany
    {
        return $this->hasMany(Reunion::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }
}
