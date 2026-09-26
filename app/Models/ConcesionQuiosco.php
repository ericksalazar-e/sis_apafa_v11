<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConcesionQuiosco extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'concesiones_quioscos';

    protected $fillable = [
        'quiosco_id',
        'empresa_id',
        'nombre_concesionario',
        'dni_ruc',
        'telefono',
        'correo',
        'fecha_inicio',
        'fecha_fin',
        'canon_mensual',
        'garantia',
        'cumple_ley_alimentacion_saludable',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'canon_mensual' => 'decimal:2',
            'garantia' => 'decimal:2',
            'cumple_ley_alimentacion_saludable' => 'boolean',
        ];
    }

    public function quiosco(): BelongsTo
    {
        return $this->belongsTo(Quiosco::class, 'quiosco_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoQuiosco::class, 'concesion_id');
    }

    /**
     * Cronograma mensual de pagos del canon de quiosco (el canon es por mes)
     */
    public function getCronogramaMensualAttribute(): array
    {
        $inicio = $this->fecha_inicio ? \Carbon\Carbon::parse($this->fecha_inicio)->startOfMonth() : \Carbon\Carbon::create(date('Y'), 3, 1);
        $fin = $this->fecha_fin ? \Carbon\Carbon::parse($this->fecha_fin)->endOfMonth() : \Carbon\Carbon::create(date('Y'), 12, 31);

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $pagos = $this->pagos;
        $cronograma = [];
        $cursor = $inicio->copy();
        $hoy = \Carbon\Carbon::now();

        while ($cursor->lte($fin)) {
            $numMes = (int) $cursor->format('n');
            $año = (int) $cursor->format('Y');
            $nombreMes = $mesesNombres[$numMes] . ' ' . $año;
            $nombreCorto = substr($mesesNombres[$numMes], 0, 3) . " '" . substr($año, -2);

            $pago = $pagos->first(function ($p) use ($mesesNombres, $numMes, $año) {
                $mesPago = mb_strtolower($p->mes_correspondiente);
                $nomBuscado = mb_strtolower($mesesNombres[$numMes]);
                return str_contains($mesPago, $nomBuscado) && (str_contains($mesPago, (string)$año) || count($mesesNombres) == 12);
            });

            $esPasado = $cursor->copy()->endOfMonth()->lt($hoy);
            $esActual = $cursor->format('Y-m') === $hoy->format('Y-m');

            $estado = 'FUTURO';
            if ($pago) {
                $estado = 'PAGADO';
            } elseif ($esPasado) {
                $estado = 'VENCIDO';
            } elseif ($esActual) {
                $estado = 'PENDIENTE';
            }

            $cronograma[] = [
                'clave' => $cursor->format('Y-m'),
                'mes_nombre' => $nombreMes,
                'mes_corto' => $nombreCorto,
                'numero_mes' => $numMes,
                'año' => $año,
                'canon' => (float) $this->canon_mensual,
                'pagado' => (bool) $pago,
                'pago' => $pago,
                'estado' => $estado,
            ];

            $cursor->addMonth();
        }

        return $cronograma;
    }

    public function getMesesPagadosCountAttribute(): int
    {
        return collect($this->cronograma_mensual)->where('pagado', true)->count();
    }

    public function getMesesPendientesCountAttribute(): int
    {
        return collect($this->cronograma_mensual)->whereIn('estado', ['PENDIENTE', 'VENCIDO'])->count();
    }

    public function getSiguienteMesPendienteAttribute(): ?string
    {
        $primerPendiente = collect($this->cronograma_mensual)->firstWhere('pagado', false);
        return $primerPendiente ? $primerPendiente['mes_nombre'] : null;
    }

    public function getTotalRecaudadoAttribute(): float
    {
        return (float) $this->pagos->sum('monto');
    }

    public function getDeudaAcumuladaAttribute(): float
    {
        $vencidos = collect($this->cronograma_mensual)->where('estado', 'VENCIDO')->count();
        return (float) ($vencidos * $this->canon_mensual);
    }
}
