<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictamenVigilancia extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'dictamenes_vigilancia';

    protected $fillable = [
        'empresa_id',
        'periodo',
        'titulo',
        'tipo_dictamen',
        'detalles_auditoria',
        'observaciones_recomendaciones',
        'monto_ingresos_auditado',
        'monto_gastos_auditado',
        'saldo_auditado',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'monto_ingresos_auditado' => 'decimal:2',
            'monto_gastos_auditado' => 'decimal:2',
            'saldo_auditado' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
