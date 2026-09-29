<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aporte extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'aportes';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'tipo',
        'descripcion',
        'monto',
        'uit_vigente',
        'max_legal',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'monto' => 'decimal:2',
            'uit_vigente' => 'decimal:2',
            'max_legal' => 'decimal:2',
        ];
    }

    public function aportesPadres(): HasMany
    {
        return $this->hasMany(AportePadre::class);
    }
}
