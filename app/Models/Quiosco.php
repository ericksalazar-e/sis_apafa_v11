<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quiosco extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'quioscos';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'ubicacion',
        'estado',
    ];

    public function concesiones(): HasMany
    {
        return $this->hasMany(ConcesionQuiosco::class, 'quiosco_id');
    }

    public function concesionVigente(): HasOne
    {
        return $this->hasOne(ConcesionQuiosco::class, 'quiosco_id')->where('estado', 'VIGENTE');
    }
}
