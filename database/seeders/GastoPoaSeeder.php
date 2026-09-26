<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\GastoPoa;
use Illuminate\Database\Seeder;

class GastoPoaSeeder extends Seeder
{
    public function run(): void
    {
        if (GastoPoa::count() > 0) {
            return;
        }

        foreach (Empresa::all() as $empresa) {
            GastoPoa::create([
                'empresa_id' => $empresa->id,
                'categoria' => 'TECNOLOGIA',
                'titulo' => 'Adquisición de Proyector Multimedia y Ecran para Aula de Innovación',
                'descripcion' => 'Aprobado en Asamblea General de Padres. Factura F001-00248.',
                'monto' => 1450.00,
                'nro_comprobante' => 'F001-00248',
                'proveedor' => 'Comercial Tecnológica del Perú SAC',
                'fecha' => now()->subDays(15),
            ]);

            GastoPoa::create([
                'empresa_id' => $empresa->id,
                'categoria' => 'DEPORTE_LUDICO',
                'titulo' => 'Implementación Deportiva: Balones y Colchonetas de Psicomotricidad',
                'descripcion' => 'Material lúdico y deportivo para Inicial y Primaria acordado en el POA.',
                'monto' => 820.00,
                'nro_comprobante' => 'B002-00512',
                'proveedor' => 'Distribuidora Deportiva Campeones EIRL',
                'fecha' => now()->subDays(8),
            ]);

            GastoPoa::create([
                'empresa_id' => $empresa->id,
                'categoria' => 'CULTURAL_TALLERES',
                'titulo' => 'Taller de Escuela de Padres y Apoyo en Movilidad Olimpiada ONEM',
                'descripcion' => 'Sesión con psicóloga sobre convivencia escolar y viáticos para escolares clasificados.',
                'monto' => 550.00,
                'nro_comprobante' => 'B001-00109',
                'proveedor' => 'Servicios Educativos y Transporte San Martín',
                'fecha' => now()->subDays(3),
            ]);

            GastoPoa::create([
                'empresa_id' => $empresa->id,
                'categoria' => 'EMERGENCIAS_BIENESTAR',
                'titulo' => 'Reposición de Insumos y Equipamiento para Botiquín de Primeros Auxilios',
                'descripcion' => 'Alcohol, gasas, vendas, termómetros y medicamentos de urgencia básica.',
                'monto' => 280.00,
                'nro_comprobante' => 'B003-00941',
                'proveedor' => 'Botica & Salud San José',
                'fecha' => now()->subDay(),
            ]);
        }
    }
}
