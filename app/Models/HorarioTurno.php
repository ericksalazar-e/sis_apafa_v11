<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioTurno extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'horarios_turnos';

    protected $fillable = [
        'empresa_id',
        'turno',
        'hora_ingreso',
        'hora_limite_puntual',
        'hora_salida',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Obtiene los horarios configurados para la empresa o genera los predeterminados.
     */
    public static function getHorariosDefault(): array
    {
        return [
            'Mañana' => [
                'hora_ingreso' => '07:30',
                'hora_limite_puntual' => '08:00',
                'hora_salida' => '13:00',
                'activo' => true,
            ],
            'Tarde' => [
                'hora_ingreso' => '13:00',
                'hora_limite_puntual' => '13:30',
                'hora_salida' => '18:30',
                'activo' => true,
            ],
            'Noche' => [
                'hora_ingreso' => '18:30',
                'hora_limite_puntual' => '19:00',
                'hora_salida' => '22:30',
                'activo' => false,
            ],
        ];
    }

    /**
     * Retorna o inicializa los registros de horarios para la empresa actual.
     */
    public static function sincronizarYObtener(int $empresaId)
    {
        $defaults = self::getHorariosDefault();
        $existentes = self::where('empresa_id', $empresaId)->get()->keyBy('turno');

        foreach ($defaults as $turno => $data) {
            if (! $existentes->has($turno)) {
                $creado = self::create([
                    'empresa_id' => $empresaId,
                    'turno' => $turno,
                    'hora_ingreso' => $data['hora_ingreso'],
                    'hora_limite_puntual' => $data['hora_limite_puntual'],
                    'hora_salida' => $data['hora_salida'],
                    'activo' => $data['activo'],
                ]);
                $existentes->put($turno, $creado);
            }
        }

        return $existentes;
    }
}
